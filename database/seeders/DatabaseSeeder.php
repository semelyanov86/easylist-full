<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->premium()->create([
            'name' => 'Sergey Emelyanov',
            'email' => 'se@sergeyem.ru',
            'about_me' => <<<'TEXT'
                Sergei Emelyanov
                Phone: +4915211100235
                E-mail: me@sergeyem.eu

                Cover Letter
                Dear Hiring Manager,

                I am highly interested in Software Developer position as I see an amazing opportunity to contribute my professional abilities to your Company and have a positive impact on software development. I embrace new goals and would be glad to drive complex projects and design effective solutions.

                My experience in IT is over 12 years with more than 10 years in Software Development and 5+ years in the role of Tech Lead. My portfolio currently includes more than 30 commercially successful projects on the base of Symfony/Laravel/Vue.js.

                Being an experienced Software Developer I am focused on finding and implementing the best solutions. I keep track of the latest trends and advanced technologies working with the following stack: Golang, PHP, JavaScript, Git, Docker, Vue.js, React.js, Redis, Laravel, Vue.js, Node.js, Vtiger CRM, jQuery, Ajax. I am confident in developing and verifying program code as well as reviewing code developed by others.

                I have a sound ability to recruit and lead a team providing technical advice and monitoring teamwork. I am highly organized and have an ability to prioritize and multitask while being in a fast-paced environment.

                My engineering mindset combined with excellent communication and presentation skills allows me to build effective relationships with clients and internal stakeholders. I am fluent in English, German and can freely interact with foreign customers and colleagues.

                I am enthusiastic about bringing my skills and expertise to your dynamic company. Please find my CV attached.

                Sincerely,
                Sergei Emelyanov
                TEXT,
            'is_premium' => true,
            'ticktick_list_id' => '69a93bb88f08311dd1cd451a',
            'ticktick_token' => env('TICKTICK_DEFAULT_TOKEN'),
        ]);

        $this->call(JobStatusSeeder::class);
        $this->call(JobCategorySeeder::class);
        $this->call(SkillSeeder::class);
        $this->call(ShoppingListLegacySeeder::class);
        $this->call(JobListingLegacySeeder::class);
    }
}
