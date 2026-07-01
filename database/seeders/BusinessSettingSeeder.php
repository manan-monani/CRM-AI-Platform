<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Branding
            'business_name' => 'Nexus Global Corp',
            'tagline' => 'Innovating the future of enterprise',
            'industry' => 'Technology & SaaS',
            'primary_color' => '#2563eb', // Blue

            // Logos & Assets (Using Dicebear and Unsplash for demo consistency)
            'logo_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=NE&backgroundColor=0284c7&textColor=ffffff',
            'logo_dark_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=NE&backgroundColor=1e293b&textColor=ffffff',
            'favicon_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=N&backgroundColor=2563eb&textColor=ffffff',
            'favicon_dark_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=N&backgroundColor=1e293b&textColor=ffffff',
            'cover_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=2000',

            // Colors
            'primary' => '#2563eb',
            'primary_light' => '#eff6ff',
            'primary_dark_text' => '#60a5fa',
            'secondary' => '#64748b',
            'sidebar_rail_bg' => '#1e3a8a',
            'sidebar_rail_bg_dark' => '#172554',
            'sidebar_icon_color' => '#ffffffff',
            'sidebar_icon_color_dark' => '#ffffffff',

            // Contact
            'contact_email' => 'contact@nexus-global.io',
            'contact_phone' => '+1 (555) 000-1234',
            'contact_address' => 'San Francisco, CA 94103',

            // Social
            'social_facebook' => '',
            'social_twitter' => '',
            'social_instagram' => '',


            // Legal Pages
            'privacy_policy' => '<h1>Privacy Policy</h1><p>At Nexus Global Corp, we take your privacy seriously. This policy describes how we collect, use, and protect your personal data when you use our enterprise CRM platform.</p><h2>1. Data Collection</h2><p>We collect information you provide directly to us, such as when you create an account, update your profile, or contact support.</p><h2>2. Use of Information</h2><p>We use the information we collect to maintain and improve our services, communicate with you, and protect Nexus Global Corp and our users.</p>',
            'terms_and_conditions' => '<h1>Terms and Conditions</h1><p>Welcome to Nexus Global Corp. By using our services, you agree to comply with and be bound by the following terms and conditions of use.</p><h2>1. Service Agreement</h2><p>Nexus Global Corp provides an enterprise-grade CRM solution. You agree to use this service only for lawful purposes in accordance with these Terms.</p><h2>2. User Obligations</h2><p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</p>',
            'about_us' => '<h1>About Us</h1><p>Nexus Global Corp is a leading provider of enterprise CRM solutions, dedicated to helping businesses innovate and thrive in an increasingly digital world.</p><p>Founded in 2024, our mission is to empower teams with cutting-edge technology and insightful data analytics to drive growth and foster long-lasting customer relationships.</p>',

            // Home Page Builder Sections
            'home_page_sections' => json_encode([
                [
                    'id' => '1',
                    'type' => 'hero',
                    'isOpen' => false,
                    'data' => [
                        'title_main' => 'Manage Your Sales',
                        'title_gradient' => 'With Zero Gravity.',
                        'subtitle' => 'Nexus CRM is the first predictive sales platform that automates your follow-ups and identifies million-dollar opportunities before they disappear.',
                        'cta_text' => 'Start Your Free Trial',
                        'cta_link' => '/register',
                        'image' => '',
                    ]
                ],
                [
                    'id' => '2',
                    'type' => 'stats',
                    'isOpen' => false,
                    'data' => [
                        'title' => 'Growth by the numbers',
                        'items' => [
                            ['label' => 'Active Users', 'value' => '25,000', 'suffix' => '+'],
                            ['label' => 'Deals Closed', 'value' => '$1.2B', 'suffix' => '+'],
                            ['label' => 'Global Uptime', 'value' => '99.99', 'suffix' => '%'],
                        ]
                    ]
                ],
                [
                    'id' => '3',
                    'type' => 'features',
                    'isOpen' => false,
                    'data' => [
                        'title' => 'Everything you need to scale.',
                        'description' => 'Stop juggling tabs and spreadsheet hacks. Nexus brings your entire revenue operations into a single, beautiful dashboard.',
                        'items' => [
                            [
                                'title' => 'Smart Lead Capture',
                                'description' => 'Automatically capture and qualify leads from any source with AI-powered scoring.',
                                'icon' => 'Zap'
                            ],
                            [
                                'title' => 'Interactive Deal Pipeline',
                                'description' => 'Visualize your sales process and move deals through custom stages with drag-and-drop ease.',
                                'icon' => 'Layout'
                            ],
                            [
                                'title' => 'Instant Reminders',
                                'description' => 'Never miss a follow-up with intelligent task scheduling and automated notifications.',
                                'icon' => 'Clock'
                            ]
                        ]
                    ]
                ],
                [
                    'id' => '4',
                    'type' => 'cta',
                    'isOpen' => false,
                    'data' => [
                        'title' => 'Ready to accelerate your revenue pipeline?',
                        'subtitle' => 'Join 2,000+ high-growth companies closing deals faster with Nexus CRM. No credit card required.',
                        'button_text' => 'Start for Free',
                        'button_link' => '/register',
                        'secondary_button_text' => 'Contact Sales',
                        'show_trust_badges' => true,
                        'trust_text' => 'Trusted by top-tier SDRs'
                    ]
                ]
            ]),

            // Lead Generation Page Content
            'landing_title' => 'Empower Your Business Growth',
            'landing_subtitle' => 'The modern CRM solution built for high-performance teams to capture, manage and close more deals. Join 25,000+ businesses already growing with Nexus CRM.',
            'landing_hero_image' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&q=80&w=2000',

            'landing_feature_1_title' => 'Centralized CRM',
            'landing_feature_1_description' => 'One place for all your leads and deals. No more spreadsheets.',

            'landing_feature_2_title' => 'Smart Automation',
            'landing_feature_2_description' => 'Automate your follow-ups and focus on closing deals.',

            'landing_feature_3_title' => 'Enterprise Security',
            'landing_feature_3_description' => 'Bank-grade security for your sensitive customer data.',

            'landing_feature_4_title' => 'Global Analytics',
            'landing_feature_4_description' => 'Real-time insights across 120+ countries.',
        ];

        foreach ($settings as $key => $value) {
            BusinessSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
            );
        }

        $this->command->info('✓ Seeded business settings and branding data.');
    }
}