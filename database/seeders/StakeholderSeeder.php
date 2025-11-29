<?php

namespace Database\Seeders;

use App\Models\Stakeholder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StakeholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stakeholders = [
            [
                'name' => 'Dian Siswarini',
                'email' => 'dian.siswarini@xl.co.id',
                'position' => 'Presiden Direktur & CEO',
                'department' => 'Board of Directors',
                'receive_alerts' => true,
            ],
            [
                'name' => 'Yessie D. Yosetya',
                'email' => 'yessie.yosetya@xl.co.id',
                'position' => 'Director & Chief Enterprise Business and Corporate Affairs Officer',
                'department' => 'Middle Management',
                'receive_alerts' => true,
            ],
            [
                'name' => 'I Gede Darmayusa',
                'email' => 'gede.darmayusa@xl.co.id',
                'position' => 'Director & Chief Technology Officer',
                'department' => 'Middle Management',
                'receive_alerts' => true,
            ],
            [
                'name' => 'Reza Zahid Mirza',
                'email' => 'reza.mirza@xl.co.id',
                'position' => 'Group Head Corporate Communications & Sustainability',
                'department' => 'Middle Management',
                'receive_alerts' => true,
            ],
            [
                'name' => 'Ratu Maulia Ommaya Firhah',
                'email' => 'ratu.firhah@xl.co.id',
                'position' => 'Head of Sustainability',
                'department' => 'Operational Teams',
                'receive_alerts' => true,
            ],
            [
                'name' => 'Risky Fauzi Widodo',
                'email' => 'risky.widodo@xl.co.id',
                'position' => 'Climate Change and Decarbonization Strategist',
                'department' => 'Operational Teams',
                'receive_alerts' => true,
            ],
        ];

        foreach ($stakeholders as $stakeholder) {
            Stakeholder::create($stakeholder);
        }
    }
}
