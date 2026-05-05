<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Job;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Owner account ──────────────────────────────────────────────────
        $owner = User::create([
            'name'    => 'Alex Johnson',
            'email'   => 'admin@fieldcrm.test',
            'password'=> Hash::make('password'),
            'company' => 'Johnson Contracting',
            'phone'   => '+1 (555) 001-0001',
            'role'    => 'owner',
        ]);

        // ── Team members ──────────────────────────────────────────────────
        User::create([
            'name'    => 'Sarah Martinez',
            'email'   => 'sarah@fieldcrm.test',
            'password'=> Hash::make('password'),
            'role'    => 'admin',
        ]);

        User::create([
            'name'    => 'Mike Chen',
            'email'   => 'mike@fieldcrm.test',
            'password'=> Hash::make('password'),
            'role'    => 'member',
        ]);

        // ── Customers ────────────────────────────────────────────────────
        $customers = [
            ['name' => 'Robert & Linda Wilson',  'email' => 'wilson@email.com',     'phone' => '(555) 234-5678', 'company' => '',                   'status' => 'Active',   'type' => 'Residential', 'address' => '456 Oak Lane, Springfield, IL 62702',    'lifetime_value' => 8450.00,  'total_jobs' => 3, 'tags' => ['HVAC', 'Loyal']],
            ['name' => 'Tech Solutions Inc',      'email' => 'facilities@techsol.com','phone' => '(555) 345-6789', 'company' => 'Tech Solutions Inc', 'status' => 'VIP',      'type' => 'Commercial',  'address' => '789 Business Park Dr, Chicago, IL 60601', 'lifetime_value' => 24800.00, 'total_jobs' => 7, 'tags' => ['Commercial', 'VIP']],
            ['name' => 'Maria Santos',            'email' => 'maria.s@gmail.com',    'phone' => '(555) 456-7890', 'company' => '',                   'status' => 'Active',   'type' => 'Residential', 'address' => '321 Maple St, Aurora, IL 60505',          'lifetime_value' => 3200.00,  'total_jobs' => 2, 'tags' => ['Plumbing']],
            ['name' => 'Downtown Restaurant Group','email' => 'ops@dtrestaurant.com', 'phone' => '(555) 567-8901', 'company' => 'DRG LLC',           'status' => 'Active',   'type' => 'Commercial',  'address' => '100 Main St, Chicago, IL 60605',          'lifetime_value' => 15600.00, 'total_jobs' => 5, 'tags' => ['HVAC', 'Recurring']],
            ['name' => 'James & Patricia Brown',  'email' => 'jpbrown@yahoo.com',    'phone' => '(555) 678-9012', 'company' => '',                   'status' => 'Inactive', 'type' => 'Residential', 'address' => '654 Elm Ave, Joliet, IL 60432',           'lifetime_value' => 1100.00,  'total_jobs' => 1, 'tags' => ['Roofing']],
        ];

        $createdCustomers = [];
        foreach ($customers as $c) {
            $createdCustomers[] = Customer::create(array_merge($c, ['user_id' => $owner->id]));
        }

        // ── Jobs ────────────────────────────────────────────────────────
        $jobData = [
            ['customer_id' => $createdCustomers[0]->id, 'title' => 'HVAC Annual Maintenance',     'type' => 'HVAC',       'status' => 'Scheduled',   'assigned_to' => 'Mike Chen',    'scheduled_date' => '2025-02-15', 'value' => 850.00,   'progress' => 0],
            ['customer_id' => $createdCustomers[1]->id, 'title' => 'Office Building Rewire',      'type' => 'Electrical', 'status' => 'In Progress', 'assigned_to' => 'Sarah Martinez','scheduled_date' => '2025-02-10', 'value' => 12400.00, 'progress' => 65],
            ['customer_id' => $createdCustomers[2]->id, 'title' => 'Kitchen Plumbing Repair',     'type' => 'Plumbing',   'status' => 'Completed',   'assigned_to' => 'Mike Chen',    'scheduled_date' => '2025-01-28', 'value' => 1200.00,  'progress' => 100],
            ['customer_id' => $createdCustomers[3]->id, 'title' => 'Restaurant AC Installation',  'type' => 'HVAC',       'status' => 'In Progress', 'assigned_to' => 'Alex Johnson', 'scheduled_date' => '2025-02-08', 'value' => 6800.00,  'progress' => 40],
            ['customer_id' => $createdCustomers[4]->id, 'title' => 'Roof Inspection & Repair',    'type' => 'Roofing',    'status' => 'Completed',   'assigned_to' => 'Mike Chen',    'scheduled_date' => '2025-01-20', 'value' => 1100.00,  'progress' => 100],
        ];

        foreach ($jobData as $j) {
            Job::create(array_merge($j, ['user_id' => $owner->id]));
        }

        // ── Leads ───────────────────────────────────────────────────────
        $leadsData = [
            ['name' => 'David Thompson',    'email' => 'd.thompson@gmail.com', 'phone' => '(555) 111-2222', 'source' => 'Website',   'status' => 'New',         'priority' => 'High',   'value' => 5500.00,  'service_type' => 'Electrical'],
            ['name' => 'Green Valley HOA',  'email' => 'admin@gvhoa.com',      'phone' => '(555) 222-3333', 'source' => 'Referral',  'status' => 'Qualified',   'priority' => 'High',   'value' => 18000.00, 'service_type' => 'HVAC'],
            ['name' => 'Lisa Patel',        'email' => 'lisa.p@outlook.com',   'phone' => '(555) 333-4444', 'source' => 'Google Ads','status' => 'Contacted',   'priority' => 'Medium', 'value' => 2200.00,  'service_type' => 'Plumbing'],
            ['name' => 'Harbor View Hotel', 'email' => 'maintenance@hvh.com',  'phone' => '(555) 444-5555', 'source' => 'Referral',  'status' => 'Proposal',    'priority' => 'High',   'value' => 32000.00, 'service_type' => 'Electrical'],
            ['name' => 'Carlos Rivera',     'email' => 'c.rivera@yahoo.com',   'phone' => '(555) 555-6666', 'source' => 'Facebook',  'status' => 'New',         'priority' => 'Low',    'value' => 800.00,   'service_type' => 'Roofing'],
            ['name' => 'Sunrise Daycare',   'email' => 'owner@sunrise.com',    'phone' => '(555) 666-7777', 'source' => 'Website',   'status' => 'Negotiation', 'priority' => 'Medium', 'value' => 9500.00,  'service_type' => 'HVAC'],
        ];

        foreach ($leadsData as $l) {
            Lead::create(array_merge($l, ['user_id' => $owner->id]));
        }
    }
}