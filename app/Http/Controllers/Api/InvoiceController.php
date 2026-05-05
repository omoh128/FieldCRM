<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::forOwner($request->user()->id)
            ->with('job:id,title', 'lead:id,first_name,last_name,company');

        if ($status = $request->query('status')) $query->status($status);
        if ($search = $request->query('search')) {
            $query->whereHas('lead', fn($q) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('company',    'like', "%{$search}%")
            )->orWhere('invoice_number', 'like', "%{$search}%");
        }

        return response()->json(
            $query->latest()->paginate($request->query('per_page', 100))
        );
    }

    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorise($request, $invoice);
        return response()->json(
            $invoice->load('job', 'lead', 'payments')
        );
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorise($request, $invoice);

        $data = $request->validate([
            'status'   => ['nullable', 'string', 'in:unpaid,partial,paid,overdue'],
            'due_date' => ['nullable', 'date'],
            'notes'    => ['nullable', 'string'],
            'amount'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $invoice->update($data);
        return response()->json($invoice->load('job', 'lead'));
    }

    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorise($request, $invoice);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted.']);
    }

    /**
     * Create a Stripe Checkout Session for this invoice.
     */
    public function stripeCheckout(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorise($request, $invoice);
        abort_if($invoice->isPaid(), 422, 'Invoice is already paid.');

        $stripeKey = config('services.stripe.secret');
        abort_if(!$stripeKey, 500, 'Stripe secret key not configured.');

        Stripe::setApiKey($stripeKey);

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount'  => (int) round($invoice->balance() * 100),
                    'product_data' => [
                        'name' => "Invoice {$invoice->invoice_number}",
                        'description' => $invoice->job->title ?? '',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => config('app.url') . "/invoices/{$invoice->id}?paid=1",
            'cancel_url'  => config('app.url') . "/invoices/{$invoice->id}?cancelled=1",
            'metadata'    => ['invoice_id' => $invoice->id],
        ]);

        $invoice->update(['stripe_checkout_session_id' => $session->id]);

        return response()->json(['checkout_url' => $session->url]);
    }

    /**
     * Stripe webhook — marks invoice paid when payment succeeds.
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session   = $event->data->object;
            $invoiceId = $session->metadata->invoice_id ?? null;
            $invoice   = Invoice::find($invoiceId);

            if ($invoice && !$invoice->isPaid()) {
                $amount = $session->amount_total / 100;

                Payment::create([
                    'invoice_id'     => $invoice->id,
                    'owner_id'       => $invoice->owner_id,
                    'amount'         => $amount,
                    'method'         => 'stripe',
                    'transaction_id' => $session->payment_intent,
                    'status'         => 'completed',
                    'payment_date'   => now()->toDateString(),
                ]);

                $invoice->update([
                    'paid_amount' => $invoice->paid_amount + $amount,
                    'status'      => 'paid',
                    'paid_date'   => now()->toDateString(),
                    'stripe_payment_intent_id' => $session->payment_intent,
                ]);
            }
        }

        return response()->json(['received' => true]);
    }

    public function stats(Request $request): JsonResponse
    {
        $ownerId = $request->user()->id;
        return response()->json([
            'total'       => Invoice::forOwner($ownerId)->count(),
            'unpaid'      => Invoice::forOwner($ownerId)->status('unpaid')->count(),
            'paid'        => Invoice::forOwner($ownerId)->status('paid')->count(),
            'overdue'     => Invoice::forOwner($ownerId)->status('overdue')->count(),
            'total_value' => Invoice::forOwner($ownerId)->sum('amount'),
            'paid_value'  => Invoice::forOwner($ownerId)->sum('paid_amount'),
            'outstanding' => Invoice::forOwner($ownerId)->whereIn('status', ['unpaid','partial','overdue'])->sum(\DB::raw('amount - paid_amount')),
        ]);
    }

    private function authorise(Request $request, Invoice $invoice): void
    {
        abort_if($invoice->owner_id !== $request->user()->id, 403, 'Unauthorized.');
    }
}