<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * Импорт вакансий из старой системы (Excel-выгрузка).
 */
class JobListingLegacySeeder extends Seeder
{
    /** Маппинг статусов Excel → заголовки в БД. */
    private const array STATUS_MAP = [
        'Saved' => 'Отложено',
        'Applied' => 'Подана заявка',
        'Interviewing' => 'Первичное собеседование',
        'Offer' => 'Оффер',
        'Rejected' => 'Отклонено',
        'RejectedAfterInterview' => 'Отклонено после собеседования',
    ];

    public function run(): void
    {
        $user = User::firstOrFail();

        // Категория для импортированных вакансий
        $category = JobCategory::query()->firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Fullstack Version 4'],
            ['currency' => 'eur'],
        );

        // Кеш статусов
        /** @var array<string, int> $statusMap */
        $statusMap = $user->jobStatuses()->pluck('id', 'title')->all();

        Job::withoutEvents(function () use ($user, $category, $statusMap): void {
            foreach ($this->getData() as $row) {
                $hasInterview = $row['interviewed'] !== null;
                $statusKey = $row['status'];

                // Rejected после собеседования → другой статус
                if ($statusKey === 'Rejected' && $hasInterview) {
                    $statusKey = 'RejectedAfterInterview';
                }

                $statusTitle = self::STATUS_MAP[$statusKey];
                $statusId = $statusMap[$statusTitle] ?? throw new \RuntimeException(
                    "Статус «{$statusTitle}» не найден",
                );

                $createdAt = $this->excelToCarbon($row['created']);

                $description = $row['description'] ?? $this->getDescriptionByUrl($row['url'] ?? '');

                $job = Job::query()->create([
                    'user_id' => $user->id,
                    'uuid' => Str::uuid()->toString(),
                    'job_status_id' => $statusId,
                    'job_category_id' => $category->id,
                    'title' => $row['title'],
                    'description' => $description,
                    'company_name' => $row['company'],
                    'location_city' => $row['location'],
                    'salary' => $row['salary'] ?: null,
                    'job_url' => $row['url'] ?: null,
                    'resume_version_url' => '4',
                    'is_favorite' => false,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Задачи по датам
                $this->createTasks($job, $user, $row, $createdAt);

                // Запись в аудит
                $this->createAuditEntry($job, $user, $createdAt);
            }
        });
    }

    /**
     * Создание задач по датам-вехам.
     */
    private function createTasks(Job $job, User $user, array $row, Carbon $createdAt): void
    {
        $milestones = [
            ['key' => 'applied', 'title' => 'Заявка отправлена'],
            ['key' => 'interviewed', 'title' => 'Собеседование пройдено'],
            ['key' => 'offered', 'title' => 'Оффер получен'],
            ['key' => 'rejected', 'title' => 'Отказ получен'],
        ];

        foreach ($milestones as $milestone) {
            $serial = $row[$milestone['key']] ?? null;
            if ($serial === null) {
                continue;
            }

            $date = $this->excelToCarbon($serial);

            JobTask::query()->create([
                'job_id' => $job->id,
                'user_id' => $user->id,
                'title' => $milestone['title'],
                'completed_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    /**
     * Запись аудита «Вакансия создана».
     */
    private function createAuditEntry(Job $job, User $user, Carbon $createdAt): void
    {
        Activity::query()->create([
            'log_name' => 'job',
            'description' => 'Вакансия создана',
            'subject_type' => Job::class,
            'subject_id' => $job->id,
            'event' => 'created',
            'causer_type' => User::class,
            'causer_id' => $user->id,
            'properties' => [
                'attributes' => [
                    'title' => $job->title,
                    'company_name' => $job->company_name,
                    'location_city' => $job->location_city,
                    'salary' => $job->salary,
                    'job_url' => $job->job_url,
                    'resume_version_url' => $job->resume_version_url,
                    'job_status_id' => $job->job_status_id,
                    'job_category_id' => $job->job_category_id,
                ],
            ],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    /**
     * Конвертация Excel serial date → Carbon.
     */
    private function excelToCarbon(float $serial): Carbon
    {
        return Carbon::createFromTimestamp((int) (($serial - 25569) * 86400));
    }

    /**
     * Описание вакансии по URL.
     */
    private function getDescriptionByUrl(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        foreach ($this->getDescriptions() as $pattern => $description) {
            if (str_contains($url, $pattern)) {
                return $description;
            }
        }

        return null;
    }

    /**
     * Описания вакансий, полученные с сайтов работодателей.
     *
     * @return array<string, string>
     */
    private function getDescriptions(): array
    {
        return [
            'stillfrontgroup.teamtailor.com' => <<<'MD'
                ## PHP Software Engineer — Stillfront Group (Payment Hub)

                **Standort:** Hamburg

                ### Overview
                Stillfront's successful Payment Hub is looking for a Software Engineer to develop, improve, and maintain their payment service systems. Your primary responsibility is to ensure they have a maintainable and scalable product while delivering new features in line with the product strategy.

                ### Collaboration
                You will work closely with the Payment, Fraud, Game and Customer Service teams to facilitate the strategic alignment.

                ### Key Responsibilities
                - Independently develop and improve the payment system, including payment service providers, backend systems, services and APIs
                - Develop applications and services from the infrastructure to the backend and frontend
                - Take responsibility for the complete software lifecycle (cooperation with stakeholders, architecture, development, automated testing, running and monitoring)

                ### Tech Stack
                - Amazon Web Services
                - Docker
                - PHP 8.0+ and corresponding frameworks
                - Application quality through testing and pair programming
                MD,
            'audaris.de' => <<<'MD'
                ## PHP-Backend-Entwickler (m/w/d) — audaris GmbH

                **Standort:** Neutraubling | **Arbeitsform:** Mobiles Arbeiten, flexible Arbeitszeiten

                ### Über audaris als Arbeitgeber
                Wir konzentrieren uns auf das Wesentliche und verlieren unsere Ziele nicht aus den Augen. Bei audaris hat jeder ein Ziel: den europaweiten Fahrzeughandel mit unseren Tools täglich ein Stück moderner, effizienter und erfolgreicher zu machen.

                ### Deine Aufgaben
                - Gemeinsam im Team entwickelst du Web-Applikationen für die Automobilbranche
                - Verwendete Technologien: Node.js, JavaScript (VUE), PHP, Kubernetes, Docker
                - Datenbanken: Mongo DB, Maria DB, PostgreSQL
                - Du bringst eigene Ideen ein und unterstützt aktiv das Produktmanagement-Team
                - Unterstützung bei der API-Entwicklung mit Node.js: Restful API

                ### Dein Profil
                - Fundierte Kenntnisse in der Backend und Frontend Programmierung
                - Node.js, JavaScript (VUE), PHP, Kubernetes, Docker
                - Mongo DB, Maria DB, PostgreSQL
                - Informatikstudium oder eine vergleichbare Ausbildung
                - Selbständiges Arbeiten, kommunikativ und teamfähig

                ### Deine Vorteile bei audaris
                - Mobiles Arbeiten und flexible Arbeitszeiten
                - 30 Tage Urlaub, betriebliche Altersvorsorge, Fahrrad Leasing
                - Fachliche und persönliche Weiterbildung
                - Digitales Zeitmanagement mit Überstundenausgleich
                MD,
            'kosatec.de' => <<<'MD'
                ## Web Entwickler - Fokus PHP (m/w/d) — KOSATEC

                **Standort:** Braunschweig, Remote

                ### Über KOSATEC
                KOSATEC ist einer der erfolgreichsten IT-Distributoren in Niedersachsen mit 300 Mitarbeitern an 8 Standorten und 555 Millionen Euro Umsatz.

                ### Deine Aufgaben
                - Weiterentwicklung des Onlineshops sowie Erstellung von Websites
                - Entwicklung von internen und externen Webapplikationen
                - Programmierung von Schnittstellen zu externen Systemen
                - Konzeption und Entwicklung von Datenbankstrukturen und User-Interfaces
                - Analyse und Optimierung hinsichtlich Sicherheit, Performance und Usability

                ### Dein Profil
                - Mehrjährige Berufserfahrung, gute Kenntnisse in PHP und Laravel
                - Kenntnisse in HTML, CSS, JavaScript, SQL
                - Sehr gutes Verständnis für webbasierte Architekturen
                - Sehr gute Deutschkenntnisse (mindestens C1)

                ### Benefits
                - Flexible Arbeitszeit, 30 Tage Urlaub
                - Überdurchschnittliche Bezahlung, Weiterbildung & Coachings
                - Hansefit, betriebliche Altersvorsorge, JobRad
                MD,
            'digitale-vignette-online/14909432' => <<<'MD'
                ## Senior Webentwickler:in — DMC Digital Maut Consulting GmbH

                **Standort:** Münster (Remote möglich) | **Firmenwagen:** Tesla

                ### Aufgaben
                - Betreuung und Weiterentwicklung bestehender Laravel-Anwendungen (PHP 8)
                - Konzeption und Umsetzung neuer Features
                - Fokus auf modernes Software-Engineering und KI-gestützte Entwicklungsmethoden

                ### Anforderungen
                - Mehrjährige Erfahrung mit Laravel und PHP 8
                - JavaScript, HTML, CSS (Vue.js ist ein Plus)
                - Git und Pull-Request-basierte Workflows
                - Offenheit für KI-gestützte Entwicklung

                ### Benefits
                - Tesla Firmenwagen, flexible Arbeitszeiten, Full Remote möglich
                - Gehalt: 36.000 – 60.000+ EUR/Jahr
                MD,
            'eworks.de' => <<<'MD'
                ## PHP-Entwickler (Festanstellung) — eWorks

                **Standort:** Frankfurt am Main | **Home-Office:** bis 100%

                ### Über eWorks
                Gegründet 1998, Team von ca. 50 Technikbegeisterten, über 25 Jahre Softwareentwicklung.

                ### Aufgaben
                - Entwicklung individueller PHP-Applikationen mit Laminas, Symfony oder Laravel
                - Entwicklung individueller Onlineshops mit OXID eShop oder Shopware
                - Entwicklung individueller CMS-Websites mit TYPO3 oder WordPress
                - Eigenverantwortliche Durchführung kleinerer Projekte

                ### Benefits
                - Unbefristeter Arbeitsvertrag, flexible Zeiteinteilung
                - Home-Office zu 100%, echte 40-Stunden-Woche
                - Überstunden 100% vergütet
                - Top-Arbeitsplatz, kein Dress-Code
                - Weiterbildung (800+ Bücher), keine Rufbereitschaft
                MD,
            'raisenow' => <<<'MD'
                ## (Senior) Software Engineer (f/d/m) PHP & Payments — RaiseNow

                **Standort:** Zürich, Berlin, Brüssel oder Full Remote | **Umfang:** 80–100%

                ### Über RaiseNow
                Fundraising-Plattform für Non-Profit-Organisationen. Kunden: Save the Children, WWF, Amnesty, UNICEF.

                ### Aufgaben
                - Payment-Integrationen erweitern (PayPal, Stripe u.a.)
                - Payment-Microservices und Systemarchitektur vorantreiben
                - Clean Code, Team-Mentoring

                ### Anforderungen
                - PHP und Symfony-Ökosystem
                - Erfahrung mit Payment-Service-Providern
                - RESTful APIs und event-driven Microservices
                - Unit- und Functional Testing, Continuous Delivery

                ### Benefits
                - 5 Wochen Urlaub + PTO-Tage, Sabbatical-Optionen
                - Kostenlose psychologische Beratung
                - Regelmäßige Team-Events, Workation
                MD,
            'smarttec.biz' => <<<'MD'
                ## Web-Entwickler PHP (m/w/d) — SMARTTEC

                **Standort:** Mannheim

                ### Aufgaben
                - Programmierung von Onlineshops und Websites auf WordPress-Basis
                - Entwicklung modularer Komponenten im Framework ATLAS
                - Optimierung und Betreuung bestehender Kundenwebsites
                - Code-Reviews und Qualitätskontrollen

                ### Profil
                - Gute bis sehr gute PHP-Kenntnisse
                - MySQL, GIT, HTML5, CSS3, JavaScript
                - Mind. 2 Jahre Berufserfahrung (Quereinsteiger willkommen)
                MD,
            'onoffice.com/jobs-und-bewerbung/softwareentwickler' => <<<'MD'
                ## Softwareentwickler (m/w/d) — Backend PHP — onOffice

                **Standort:** Aachen / Remote (100% Homeoffice) | **Anstellung:** Teilzeit / Vollzeit

                ### Über onOffice
                Führende cloudbasierte CRM-Software für Immobilienmakler, 35.000+ User in Europa.

                ### Aufgaben
                - Backend-Entwicklung mit PHP, Architektur-Optimierung
                - Code-Optimierung hinsichtlich Lesbarkeit, Performance und Sicherheit
                - Testautomatisierung mit PHPUnit, CI/CD-Pipeline
                - Pair Programming, Retrospektiven und Planungsmeetings

                ### Tech Stack
                PHP / PHPUnit / Git / GraphQL / MariaDB / MySQL

                ### Profil
                - Sehr gute PHP-Kenntnisse, Backend-Entwicklung
                - Git, PHPUnit, GraphQL
                - Fließende Deutschkenntnisse und gutes Englisch
                MD,
            'tegtmeier.de' => <<<'MD'
                ## PHP-Developer (m/w/d) — Tegtmeier Internet Solutions

                **Standort:** Hamburg | **Erfahrung:** 5-7 Jahre | **Gehalt:** 65.000–75.000 EUR/Jahr

                ### Aufgaben
                - OOP und Frameworkless PHP
                - Refactoring und Modernisierung bestehender Software
                - Code Reviews und Pair-Programming
                - MySQL-Datenbanken auf eigenen Linux-Servern

                ### Profil
                - Mehrjährige PHP-Entwicklungserfahrung
                - Agile Haltung, fließende Deutschkenntnisse
                - Clean Code

                ### Benefits
                - Modernes Büro am Hauptbahnhof Hamburg
                - IT-Konferenzen, Hackathons, 5 Weiterbildungstage
                - Wellpass / eGym, Massagen im Büro
                - 30 Tage Urlaub, keine Überstunden, Jobticket
                MD,
        ];
    }

    /**
     * Данные из Excel-выгрузки.
     *
     * @return list<array{company: string, title: string, status: string, created: float, applied: float|null, interviewed: float|null, offered: float|null, rejected: float|null, location: string, salary: int|null, url: string, description: string|null}>
     */
    private function getData(): array
    {
        return [
            ['company' => 'Stillfront', 'title' => 'PHP Software Engineer', 'status' => 'Saved', 'created' => 45936.4076736111, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://stillfrontgroup.teamtailor.com/jobs/6538213-php-software-engineer?promotion=1649422-trackable-share-link-php-software-engineer', 'description' => null],
            ['company' => 'Interzero', 'title' => 'Senior Full-Stack Engineer (m/f/d) - Java or PHP + React/Next.js/TypeScript', 'status' => 'Applied', 'created' => 45936.4045949074, 'applied' => 45936.4046412037, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4308242860', 'description' => null],
            ['company' => 'Limetec', 'title' => '(Senior) Backend-Softwareentwickler', 'status' => 'Applied', 'created' => 45932.422650463, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://www.stellenanzeigen.de/job/detail/20251001-15665979/', 'description' => null],
            ['company' => 'Intetics', 'title' => 'Senior PHP Engineer with strong DB experience', 'status' => 'Applied', 'created' => 45932.4143287037, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4309358753', 'description' => null],
            ['company' => 'Audaris', 'title' => 'PHP-Backend-Entwickler', 'status' => 'Applied', 'created' => 45932.3530787037, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://www.audaris.de/unternehmen/jobs/php-backend-entwickler-mwd', 'description' => null],
            ['company' => 'Kosatec', 'title' => 'Web Entwickler', 'status' => 'Interviewing', 'created' => 45932.3525, 'applied' => null, 'interviewed' => 45938.4987268519, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://karriere.kosatec.de/softwareentwicklung/webentwickler-php', 'description' => null],
            ['company' => 'Basilicom GmbH', 'title' => '(Senior) Backend PHP/Pimcore Developer [w/m/x] - DE', 'status' => 'Rejected', 'created' => 45931.2904166667, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45945.3388310185, 'location' => 'Germany', 'salary' => 68000, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4293734567', 'description' => null],
            ['company' => 'AlfaDocs.com', 'title' => 'Senior Full Stack Frontend Engineer (React + PHP)', 'status' => 'Applied', 'created' => 45931.2799884259, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4302147185', 'description' => null],
            ['company' => 'DMC Digital Maut Consulting GmbH', 'title' => 'Senior Webentwickler', 'status' => 'Applied', 'created' => 45931.2724421296, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/digitale-vignette-online/14909432-senior-webentwickler-in', 'description' => null],
            ['company' => 'eWorks', 'title' => 'PHP-Entwicklung', 'status' => 'Rejected', 'created' => 45930.5012962963, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45938.3987615741, 'location' => 'Frankfurt', 'salary' => 68000, 'url' => 'https://www.eworks.de/jobs/festanstellung/php-programmierung', 'description' => null],
            ['company' => 'PraxisConcierge', 'title' => 'Fullstack Entwickler:in', 'status' => 'Offer', 'created' => 45930.4840509259, 'applied' => null, 'interviewed' => 45930.5073263889, 'offered' => 45939.6852083333, 'rejected' => null, 'location' => 'Germany', 'salary' => 68000, 'url' => 'https://join.com/companies/praxisconcierge/14942147-fullstack-entwickler-in', 'description' => null],
            ['company' => 'Lume Strategies Company', 'title' => 'Ecommerce Developer', 'status' => 'Applied', 'created' => 45930.4259490741, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 68000, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4308171580', 'description' => null],
            ['company' => 'SEITENBAU GmbH', 'title' => 'Software Developer PHP', 'status' => 'Applied', 'created' => 45929.8048611111, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://seitenbau-gmbh.jobs.personio.de/job/2263609?display=de', 'description' => null],
            ['company' => 'ticket i/O GmbH', 'title' => '(Senior) Backend Entwickler (m/w/d) Node.js und PHP', 'status' => 'Applied', 'created' => 45929.801712963, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/ticketio/14878245-senior-backend-entwickler-m-w-d-node-js-und-php', 'description' => null],
            ['company' => 'sgalinski Internet Services', 'title' => 'WEB-DEVELOPMENT SYMFONY', 'status' => 'Applied', 'created' => 45929.7961689815, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 68000, 'url' => 'https://sgalinskiinternetservices.scope-recruiting.de/?page=job&id=3223&location=2176', 'description' => null],
            ['company' => 'RaiseNow', 'title' => '(Senior) Software Engineer (f/d/m) PHP & Payments - remote possible', 'status' => 'Applied', 'created' => 45929.7872106482, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/raisenow/14875955-senior-software-engineer-f-d-m-php-and-payments-remote-possible', 'description' => null],
            ['company' => 'Tourbook Software GmbH', 'title' => 'Full Stack Software Developer mit PHP und QA-Background', 'status' => 'Applied', 'created' => 45929.7822337963, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/tourbooksoftware/14936928-full-stack-software-developer-mit-php-und-qa-background-w-m-d', 'description' => null],
            ['company' => 'eGENTIC', 'title' => 'Senior Software Developer (m/w/divers) in Vollzeit', 'status' => 'Rejected', 'created' => 45929.7790856481, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45930.4181365741, 'location' => 'Darmstadt', 'salary' => 70000, 'url' => 'https://egentic.catsone.com/careers/13311-General/jobs/16720196-Senior-Software-Developer-mwdivers-in-Vollzeit', 'description' => null],
            ['company' => 'Betreut Zuhause GmbH', 'title' => 'Software-Entwickler', 'status' => 'Applied', 'created' => 45929.7146875, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://www.xing.com/jobs/hamburg-software-entwickler-126512441', 'description' => null],
            ['company' => 'Leifeld GmbH & Co KG', 'title' => 'Senior PHP-Entwickler', 'status' => 'Interviewing', 'created' => 45929.7141319445, 'applied' => 45929.7141666667, 'interviewed' => 45930.4298611111, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => 70000, 'url' => 'https://www.xing.com/jobs/berlin-id-1385-senior-php-entwickler-142579677', 'description' => null],
            ['company' => 'SMARTTEC', 'title' => 'Web-Entwickler PHP', 'status' => 'Applied', 'created' => 45929.587962963, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Mannheim', 'salary' => 68000, 'url' => 'https://jobs.smarttec.biz/job/web-entwickler-php/', 'description' => null],
            ['company' => 'Plotdesk GmbH', 'title' => 'Laravel Developer - KI-Plattform', 'status' => 'Applied', 'created' => 45929.5817592593, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/plotdeskcom/14930484-laravel-developer-ki-plattform-m-w-d', 'description' => null],
            ['company' => 'PCS Professional Clinical Software GmbH', 'title' => 'Senior Backend-/Full-Stack-Entwickler', 'status' => 'Applied', 'created' => 45929.5544097222, 'applied' => 45929.5544560185, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/pcs/14926121-senior-backend-full-stack-entwickler-m-w-d-abrechnung-smartlis', 'description' => null],
            ['company' => 'onOffice', 'title' => 'Softwareentwickler (m/w/d) – Backend PHP', 'status' => 'Applied', 'created' => 45929.4979050926, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://onoffice.com/jobs-und-bewerbung/softwareentwickler-m-w-d-backend-php/', 'description' => null],
            ['company' => 'easybill GmbH', 'title' => 'Senior Software Engineer PHP, TS, Rust, Java (m/w/d)', 'status' => 'Applied', 'created' => 45929.4940046296, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4293830911', 'description' => null],
            ['company' => 'InnoBrain GmbH', 'title' => 'Full-Stack-Entwickler Laravel', 'status' => 'Applied', 'created' => 45929.4864930556, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 67000, 'url' => 'https://join.com/companies/inno-brain/14939329-full-stack-entwickler-laravel-m-w-d', 'description' => null],
            ['company' => 'Tegtmeier Internet Solutions', 'title' => 'PHP-Developer', 'status' => 'Applied', 'created' => 45929.4781828704, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://www.tegtmeier.de/jobs/php-developer-mwd-hamburg', 'description' => null],
            ['company' => 'Buddy&Selly', 'title' => 'Senior PHP Symfony / Full Stack Entwickler (m/w/d)', 'status' => 'Applied', 'created' => 45929.4728935185, 'applied' => 45929.4729282407, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://reverse-retail.jobs.personio.de/job/2157234?display=de&language=de', 'description' => null],
            ['company' => 'Matomo', 'title' => 'Senior Fullstack PHP/JS Engineer - SaaS 100% Remote', 'status' => 'Rejected', 'created' => 45929.4506944444, 'applied' => 45929.4507291667, 'interviewed' => null, 'offered' => null, 'rejected' => 45936.2859259259, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://jobs.lever.co/innocraft/083a2b18-5264-4760-8433-9867d2935a37/apply', 'description' => null],
            ['company' => 'Avanquest', 'title' => 'Backend Developer', 'status' => 'Applied', 'created' => 45929.4455324074, 'applied' => 45929.4455671296, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://avanquest.bamboohr.com/careers/82', 'description' => null],
            ['company' => 'ASG', 'title' => 'Entwickler/in PHP/React', 'status' => 'Applied', 'created' => 45922.7182175926, 'applied' => 45922.718275463, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://asg.jobs.personio.de/job/2330206?language=de&src=LinkedIn&display=de', 'description' => null],
            ['company' => 'riess-ambiente.de GmbH', 'title' => 'Schnittstellen- und API-Entwickler', 'status' => 'Applied', 'created' => 45888.5615740741, 'applied' => 45888.5615740741, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Kaltenkirchen', 'salary' => 70000, 'url' => 'https://riess-ambiente-gmbh.jobs.personio.de/job/2262296?language=de&display=de', 'description' => null],
            ['company' => 'LUMASERV', 'title' => 'Principal developer / architect (Vue/Node/Laravel) [gn]', 'status' => 'Applied', 'created' => 45887.3170486111, 'applied' => 45887.3170486111, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4279591452', 'description' => null],
            ['company' => 'Schleier IT', 'title' => 'Remote PHP-Entwickler Backend', 'status' => 'Rejected', 'created' => 45883.7144675926, 'applied' => 45883.7144675926, 'interviewed' => null, 'offered' => null, 'rejected' => 45905.4216319444, 'location' => 'Germany', 'salary' => 65000, 'url' => 'https://join.com/companies/schleier/14616688-remote-php-entwickler-backend-100-home-office-flexible-arbeitszeiten', 'description' => null],
            ['company' => 'Robert Walters', 'title' => 'Softwareentwickler', 'status' => 'Interviewing', 'created' => 45880.7454398148, 'applied' => 45880.7454513889, 'interviewed' => 45902.3777083333, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.aplitrak.com/?adid=RklFVFpBLjM0Mzc2LjE1NTBAcm9iZXJ0d2FsdGVyc2RlLmFwbGl0cmFrLmNvbQ', 'description' => null],
            ['company' => 'ibelsa GmbH', 'title' => 'Backend Entwickler:in PHP', 'status' => 'Applied', 'created' => 45877.7758449074, 'applied' => 45877.7758564815, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Heusweiler', 'salary' => 70000, 'url' => 'https://join.com/companies/ibelsa/14584081-backend-entwickler-in-php-m-w-d', 'description' => null],
            ['company' => 'JUMiNGO', 'title' => 'Backend Software Engineer', 'status' => 'Rejected', 'created' => 45877.5867592593, 'applied' => 45877.5867592593, 'interviewed' => null, 'offered' => null, 'rejected' => 45890.5084837963, 'location' => 'Germany', 'salary' => null, 'url' => 'https://jumingo.kenjo.io/php25', 'description' => null],
            ['company' => 'WebID', 'title' => 'Senior Full Stack Entwickler - FinTech', 'status' => 'Rejected', 'created' => 45876.7645486111, 'applied' => 45876.7645601852, 'interviewed' => null, 'offered' => null, 'rejected' => 45888.4225, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=3841785342', 'description' => null],
            ['company' => 'Marini Systems', 'title' => 'Senior PHP Developer', 'status' => 'Applied', 'created' => 45873.7352083333, 'applied' => 45873.7352083333, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Frankfurt', 'salary' => null, 'url' => 'https://marini.systems/de/karriere/senior-php-developer/', 'description' => null],
            ['company' => 'Additive', 'title' => 'Backend Developer', 'status' => 'Rejected', 'created' => 45873.300787037, 'applied' => 45873.3007986111, 'interviewed' => 45891.2756712963, 'offered' => null, 'rejected' => 45921.7918055556, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://www.people-culture.additive.eu/en/join-the-team/backend-developer/', 'description' => null],
            ['company' => 'Kabema Consulting GmbH', 'title' => 'Senior PHP-JavaScript Entwickler', 'status' => 'Rejected', 'created' => 45871.3152199074, 'applied' => 45871.3152199074, 'interviewed' => 45875.3990162037, 'offered' => null, 'rejected' => 45883.6233101852, 'location' => 'Berlin', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4278761978', 'description' => null],
            ['company' => 'onOffice', 'title' => 'Full Stack Developer', 'status' => 'Rejected', 'created' => 45871.3112152778, 'applied' => 45871.3112268519, 'interviewed' => null, 'offered' => null, 'rejected' => 45883.3882407407, 'location' => 'Germany', 'salary' => null, 'url' => 'https://onoffice.com/jobs-und-bewerbung/full-stack-softwareentwickler-m-w-d/', 'description' => null],
            ['company' => 'leadity GmbH', 'title' => 'Senior Fullstack Developer - B2B SaaS', 'status' => 'Rejected', 'created' => 45870.7633217593, 'applied' => 45870.7633217593, 'interviewed' => null, 'offered' => null, 'rejected' => 45873.3474074074, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://join.com/companies/leadity/14607229-senior-fullstack-developer-b2b-saas-gn-hamburg-muenster', 'description' => null],
            ['company' => 'Akkodis', 'title' => 'Senior Software Engineer (m/w/d) PHP - Symfony', 'status' => 'Applied', 'created' => 45870.7609606482, 'applied' => 45870.7609606482, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4278768233', 'description' => null],
            ['company' => 'Reos', 'title' => 'Senior Software Engineer PHP', 'status' => 'Rejected', 'created' => 45867.5287847222, 'applied' => 45867.5287962963, 'interviewed' => null, 'offered' => null, 'rejected' => 45880.4485300926, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://reos.jobs.personio.de/job/1259012?language=en&display=de', 'description' => null],
            ['company' => 'Tonies', 'title' => 'Senior Fullstack Engineer (E-Commerce) (all genders)', 'status' => 'Applied', 'created' => 45865.3759490741, 'applied' => 45865.3759606482, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => null, 'url' => 'https://tonies.jobs.personio.de/job/2257585?language=en&src=LinkedIn&display=en', 'description' => null],
            ['company' => 'Käsmayr GmbH', 'title' => 'Senior Fullstack / Backend Software Developer (remote)', 'status' => 'Applied', 'created' => 45865.3699421296, 'applied' => 45865.3702083333, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/heizung-billiger/14527828-senior-fullstack-backend-software-developer-remote', 'description' => null],
            ['company' => 'Stadtraum', 'title' => 'Senior PHP Developer (Legacy Code Specialist)', 'status' => 'Applied', 'created' => 45864.3153009259, 'applied' => 45864.3153125, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => 70000, 'url' => 'https://stadtraum.jobs.personio.com/job/1427827?display=en', 'description' => null],
            ['company' => 'Creavo Projekt GmbH', 'title' => 'PHP-Webentwickler (m/w/d)', 'status' => 'Rejected', 'created' => 45864.3107291667, 'applied' => 45864.3107407407, 'interviewed' => null, 'offered' => null, 'rejected' => 45905.4218981482, 'location' => 'Elz', 'salary' => null, 'url' => 'https://join.com/companies/creavo/14526235-php-webentwickler-m-w-d-fuer-abwechslungsreiche-projekte', 'description' => null],
            ['company' => 'WinLocal GmbH', 'title' => 'Senior Fullstack PHP Developer (m/w/d) – Laravel & Vue | Remote/Berlin', 'status' => 'Interviewing', 'created' => 45864.301724537, 'applied' => 45864.301724537, 'interviewed' => 45866.7345138889, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => 70000, 'url' => 'https://winlocal.softgarden.io/job/58129102/Senior-Fullstack-PHP-Developer-m-w-d', 'description' => null],
            ['company' => 'next.motion OHG', 'title' => 'Gesucht: PHP-Entwickler und Symfony-Experten (m/w/d)', 'status' => 'Rejected', 'created' => 45863.3669560185, 'applied' => 45863.3669675926, 'interviewed' => null, 'offered' => null, 'rejected' => 45887.4243402778, 'location' => 'Gera', 'salary' => 70000, 'url' => 'https://join.com/companies/next-motion/14543495-gesucht-php-entwickler-und-symfony-experten-m-w-d', 'description' => null],
            ['company' => 'Immonu', 'title' => 'Web-Developer - PHP/Laravel', 'status' => 'Rejected', 'created' => 45863.278912037, 'applied' => 45863.278912037, 'interviewed' => 45867.6065277778, 'offered' => null, 'rejected' => 45887.3855671296, 'location' => 'Hesse', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4270631577', 'description' => null],
            ['company' => 'SALT AND PEPPER Group', 'title' => 'Softwareentwickler für Entwicklungsprojekte', 'status' => 'Rejected', 'created' => 45862.7712847222, 'applied' => 45862.7712962963, 'interviewed' => null, 'offered' => null, 'rejected' => 45873.3153356482, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://salt-and-pepper.eu/karriere/jobs/softwareentwickler-fuer-entwicklungsprojekte-all-genders/', 'description' => null],
            ['company' => 'Giffits', 'title' => 'Fullstack Software Engineer', 'status' => 'Rejected', 'created' => 45862.7673842593, 'applied' => 45862.7673958333, 'interviewed' => 45890.2722222222, 'offered' => null, 'rejected' => 45909.2953587963, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://giffits-gmbh.jobs.personio.de/job/2207817?language=de&display=de', 'description' => null],
            ['company' => 'SPACE', 'title' => 'PHP Symfony Developer', 'status' => 'Interviewing', 'created' => 45862.7634375, 'applied' => 45862.7634953704, 'interviewed' => 45891.4657175926, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4275367243', 'description' => null],
            ['company' => 'PJM Investment Akademie GmbH', 'title' => 'Lead Software Developer', 'status' => 'Interviewing', 'created' => 45862.3926736111, 'applied' => 45862.3926851852, 'interviewed' => 45874.4739699074, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://pjm.jobs.personio.de/job/2169835?display=de', 'description' => null],
            ['company' => 'yes!devs GmbH', 'title' => 'Senior Full-Stack Developer', 'status' => 'Applied', 'created' => 45862.2779398148, 'applied' => 45862.2779398148, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://yesdevs.com/job/senior-full-stack-developer-m-f-d/', 'description' => null],
            ['company' => 'PRIOjet GmbH', 'title' => 'Software Developer Full Stack', 'status' => 'Applied', 'created' => 45861.5935763889, 'applied' => 45861.593587963, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://join.com/companies/priojet/14502585-software-developer-full-stack-m-f-d', 'description' => null],
            ['company' => 'Jobgether', 'title' => 'Senior Full Stack Engineer', 'status' => 'Applied', 'created' => 45861.363900463, 'applied' => 45861.3639236111, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 75000, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4231158862', 'description' => null],
            ['company' => 'FAAREN Group', 'title' => 'Senior Backend Developer', 'status' => 'Applied', 'created' => 45861.3573726852, 'applied' => 45861.3573842593, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://faaren.jobs.personio.de/job/2032206?display=de', 'description' => null],
            ['company' => 'Thorit', 'title' => 'Senior Software Engineer', 'status' => 'Rejected', 'created' => 45861.2598032407, 'applied' => 45861.2598148148, 'interviewed' => null, 'offered' => null, 'rejected' => 45930.3434027778, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://www.thorit.com/karriere#job-2208694', 'description' => null],
            ['company' => 'ZEP GmbH', 'title' => '(Senior) PHP API Backend Developer (m/w/d) - Remote', 'status' => 'Rejected', 'created' => 45861.2542476852, 'applied' => 45861.2542476852, 'interviewed' => null, 'offered' => null, 'rejected' => 45873.5624537037, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/zep/14549965-senior-php-api-backend-developer-m-w-d-remote', 'description' => null],
            ['company' => 'Impala Search', 'title' => 'Staff Backend Engineer', 'status' => 'Applied', 'created' => 45860.5504513889, 'applied' => 45860.5504513889, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => 80000, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4257006746', 'description' => null],
            ['company' => 'hygi.de', 'title' => 'SENIOR BACKEND DEVELOPER WMS / PROZESSAUTOMATISIERUNG - PHP LARAVEL', 'status' => 'Rejected', 'created' => 45860.5447453704, 'applied' => 45860.5447453704, 'interviewed' => null, 'offered' => null, 'rejected' => 45870.2586689815, 'location' => 'Telgte', 'salary' => 70000, 'url' => 'https://www.hygi-unternehmen.de/karriere/aktuelle-jobangebote/senior-backend-developer-wms-prozessautomatisierung-php-laravel-m-w-d/', 'description' => null],
            ['company' => 'Felmo', 'title' => 'Senior PHP Laravel Developer', 'status' => 'Applied', 'created' => 45860.5398958333, 'applied' => 45860.5399074074, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => 72000, 'url' => 'https://felmo.jobs.personio.de/job/2222477?display=en', 'description' => null],
            ['company' => 'F11', 'title' => 'Senior Backend Engineer - PHP', 'status' => 'Rejected', 'created' => 45860.5378703704, 'applied' => 45860.5378703704, 'interviewed' => null, 'offered' => null, 'rejected' => 45866.3965277778, 'location' => 'Berlin', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4267758806', 'description' => null],
            ['company' => 'Anexia', 'title' => 'Senior Software Developer PHP', 'status' => 'Rejected', 'created' => 45860.3608680556, 'applied' => 45860.3608796296, 'interviewed' => null, 'offered' => null, 'rejected' => 45874.7316898148, 'location' => 'Germany', 'salary' => 72000, 'url' => 'https://anexia.com/de/unternehmen/karriere/details/senior-software-developer-php-m-w-d', 'description' => null],
            ['company' => 'creios GmbH', 'title' => 'Software Engineer PHP', 'status' => 'Rejected', 'created' => 45859.7207175926, 'applied' => 45859.7207291667, 'interviewed' => null, 'offered' => null, 'rejected' => 45881.5408101852, 'location' => 'Germany', 'salary' => 70000, 'url' => 'https://join.com/companies/creiosjobs/14502245-software-engineer-php', 'description' => null],
            ['company' => 'HomeToGo', 'title' => 'Senior Backend Engineer', 'status' => 'Rejected', 'created' => 45859.7109375, 'applied' => 45859.7109490741, 'interviewed' => null, 'offered' => null, 'rejected' => 45860.5328472222, 'location' => 'Berlin', 'salary' => 70000, 'url' => 'https://hometogo.jobs.personio.com/job/2186618?language=en&src=LinkedIn&display=en', 'description' => null],
            ['company' => 'Condat', 'title' => 'PHP Entwickler', 'status' => 'Applied', 'created' => 45859.7077546296, 'applied' => 45859.7077546296, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/collections/recommended/?currentJobId=4258190482', 'description' => null],
            ['company' => 'Digital Masters GmbH', 'title' => 'Laravel-Entwickler', 'status' => 'Rejected', 'created' => 45859.6041898148, 'applied' => 45859.6042013889, 'interviewed' => null, 'offered' => null, 'rejected' => 45862.4491666667, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://digital-masters.de/de/jobs/senior-laravel-entwickler-d-m-w', 'description' => null],
            ['company' => 'TBO', 'title' => 'Senior Backend Entwickler (m/w/d)', 'status' => 'Applied', 'created' => 45859.6014351852, 'applied' => 45859.6014467593, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => 70000, 'url' => 'https://www.xing.com/jobs/berlin-senior-backend-entwickler-138860009', 'description' => null],
            ['company' => 'webconia GmbH', 'title' => 'Web-/Softwareentwickler Backend (PHP) eCommerce (m/w/d)', 'status' => 'Applied', 'created' => 45859.5886689815, 'applied' => 45859.58875, 'interviewed' => 45859.5886805556, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4255177625', 'description' => null],
            ['company' => 'Playa Games', 'title' => 'Senior PHP Entwickler', 'status' => 'Rejected', 'created' => 45859.586412037, 'applied' => null, 'interviewed' => 45859.5864236111, 'offered' => null, 'rejected' => 45866.3861689815, 'location' => 'Hamburg', 'salary' => 73000, 'url' => 'https://playagames.teamtailor.com/jobs/6008582-senior-php-entwickler-m-w-d', 'description' => null],
            ['company' => 'Revizto', 'title' => 'Senior PHP Developer', 'status' => 'Interviewing', 'created' => 45859.5845949074, 'applied' => null, 'interviewed' => 45859.5845949074, 'offered' => null, 'rejected' => null, 'location' => 'Moscow', 'salary' => 70000, 'url' => '', 'description' => null],
            ['company' => 'WEBFADER GmbH', 'title' => 'Senior PHP-Developer (m/w/d)', 'status' => 'Rejected', 'created' => 45859.5788194445, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5788310185, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.heyjobs.co/de-de/jobs/4f3d2d0c-b4c7-411f-898e-929a8cc9129a', 'description' => null],
            ['company' => 'Zabel', 'title' => 'Senior PHP Backend Engineer', 'status' => 'Applied', 'created' => 45859.5780555556, 'applied' => 45859.5780555556, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.zabelglobal.com/de/jobs/2738-senior-php-backend-engineer-m-w-d', 'description' => null],
            ['company' => 'HanseCom', 'title' => 'Senior PHP Softwareentwickler', 'status' => 'Interviewing', 'created' => 45859.5769907407, 'applied' => null, 'interviewed' => 45859.5769907407, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://hansecom-portal.rexx-systems.com/Senior-PHP-Softwareentwickler-mwd-de-j67.html', 'description' => null],
            ['company' => 'Schüttflix', 'title' => 'Senior PHP Backend Engineer', 'status' => 'Rejected', 'created' => 45859.5758796296, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5758796296, 'location' => '', 'salary' => 75000, 'url' => 'https://www.schuettflix.com/de/de/jobs/2122951/', 'description' => null],
            ['company' => '4Elements Group', 'title' => 'Senior PHP Entwickler', 'status' => 'Applied', 'created' => 45859.5750694444, 'applied' => 45859.5750810185, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://karriere.4elements-gruppe.de/job/senior-php-entwickler-w-m-d/', 'description' => null],
            ['company' => 'Hamburg Wasser', 'title' => 'Informatiker als Fullstack-Entwickler', 'status' => 'Rejected', 'created' => 45859.5745486111, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5745486111, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://karriere.hamburgwasser.de/de/jobposting/db7fe81981f807d9ac7d84a521e9a099bd217b300/apply', 'description' => null],
            ['company' => 'format h digital GmbH', 'title' => 'Senior Backend Developer', 'status' => 'Rejected', 'created' => 45859.5737268519, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5737268519, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://join.com/companies/elbformat/14405322-senior-backend-developer-w-m-d', 'description' => null],
            ['company' => 'DZH Gmbh', 'title' => 'Fullstack Webentwickler', 'status' => 'Rejected', 'created' => 45859.5731828704, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5731944444, 'location' => '', 'salary' => null, 'url' => 'https://jobs.optadata.de/jobportal/optadata/viewAusschreibung/2025-223.html', 'description' => null],
            ['company' => 'Zellerfeld', 'title' => 'Full Stack Developer', 'status' => 'Applied', 'created' => 45859.5724074074, 'applied' => 45859.5724189815, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://zellerfeld.jobs.personio.com/job/1646674?display=en', 'description' => null],
            ['company' => 'Pirate Ship Software GmbH', 'title' => '(Senior) Backend Engineer', 'status' => 'Rejected', 'created' => 45859.5711226852, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5711226852, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4262226860', 'description' => null],
            ['company' => 'allbranded', 'title' => 'Senior Software Engineer', 'status' => 'Rejected', 'created' => 45859.5702893519, 'applied' => null, 'interviewed' => 45859.5703009259, 'offered' => null, 'rejected' => 45889.4066435185, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://allbranded.jobs.personio.de/job/2201563?language=de&display=de', 'description' => null],
            ['company' => 'Muxon', 'title' => 'PHP Developer, German-Speaking (C1/C2)', 'status' => 'Rejected', 'created' => 45859.5697337963, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5697337963, 'location' => '', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4262815986', 'description' => null],
            ['company' => 'Bornholdt Lee GmbH', 'title' => 'Softwareentwickler*in Web Development PHP (m/w/d)', 'status' => 'Rejected', 'created' => 45859.5687268519, 'applied' => 45859.5687384259, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5687962963, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://bornholdt-lee-gmbh.jobs.personio.de/job/1248683?language=de&display=de', 'description' => null],
            ['company' => 'Alemo', 'title' => 'Fullstack Entwickler', 'status' => 'Applied', 'created' => 45859.5671180556, 'applied' => 45859.5671296296, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.alemo.de/de/jobs', 'description' => null],
            ['company' => 'KoRo', 'title' => 'Senior Backend PHP Engineer', 'status' => 'Rejected', 'created' => 45859.5663194445, 'applied' => 45859.5663310185, 'interviewed' => null, 'offered' => null, 'rejected' => 45909.419224537, 'location' => 'Berlin', 'salary' => null, 'url' => 'https://koro-handels-gmbh.jobs.personio.de/job/2243269?language=en&src=LinkedIn&display=en', 'description' => null],
            ['company' => 'Vesterling AG', 'title' => 'Softwareentwickler PHP / Symfony', 'status' => 'Rejected', 'created' => 45859.5657986111, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5657986111, 'location' => '', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4219145323', 'description' => null],
            ['company' => 'Xentral ERP', 'title' => 'Senior Software Engineer Fulfillment', 'status' => 'Rejected', 'created' => 45859.5651157407, 'applied' => 45859.5651157407, 'interviewed' => 45862.2891087963, 'offered' => null, 'rejected' => 45880.5299421296, 'location' => '', 'salary' => null, 'url' => 'https://jobs.eu.lever.co/xentral/6e11daab-0d5d-467d-abca-a8687570d610', 'description' => null],
            ['company' => 'byte5 GMBH', 'title' => 'Entwickler PHP / Laravel (m/w/d)', 'status' => 'Rejected', 'created' => 45859.5643634259, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5643634259, 'location' => '', 'salary' => 71000, 'url' => 'https://www.byte5.jobs/jobs/19107581/', 'description' => null],
            ['company' => 'fino taxtech GmbH', 'title' => 'Laravel Entwickler (all genders)', 'status' => 'Rejected', 'created' => 45859.5634837963, 'applied' => 45859.5634837963, 'interviewed' => null, 'offered' => null, 'rejected' => 45880.4483333333, 'location' => 'Kassel', 'salary' => null, 'url' => 'https://www.xing.com/jobs/kassel-laravel-entwickler-all-genders-100-home-office-deutschland-136952986', 'description' => null],
            ['company' => 'Nordalux', 'title' => 'Remote Laravel Fullstack Developer (m/w/d)', 'status' => 'Applied', 'created' => 45859.5629398148, 'applied' => 45859.5629398148, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Heide', 'salary' => null, 'url' => 'https://www.xing.com/jobs/heide-remote-laravel-fullstack-developer-api-frontend-rockstar-gesucht-137976529', 'description' => null],
            ['company' => 'visunext', 'title' => 'PHP Application Developer Backend', 'status' => 'Rejected', 'created' => 45859.561875, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.561875, 'location' => '', 'salary' => null, 'url' => 'https://www.get-in-it.de/jobsuche/p198446-668bce31/668bce31', 'description' => null],
            ['company' => 'BWSolution', 'title' => 'Full Stack Web Developer', 'status' => 'Applied', 'created' => 45859.5608449074, 'applied' => 45859.5608564815, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://join.com/companies/bwsolution/14497980-full-stack-web-developer-m-w-d', 'description' => null],
            ['company' => 'Triviar Education GmbH', 'title' => 'Fullstack-Entwickler Vue.js & PHP', 'status' => 'Rejected', 'created' => 45859.5600578704, 'applied' => 45859.5600694444, 'interviewed' => null, 'offered' => null, 'rejected' => 45862.514837963, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://join.com/companies/triviar/14496208-fullstack-entwickler-vue-js-und-php-m-w-d', 'description' => null],
            ['company' => 'Verisk', 'title' => 'PHP Full Stack Developer', 'status' => 'Applied', 'created' => 45859.5593055556, 'applied' => 45859.5593055556, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Berlin', 'salary' => null, 'url' => 'https://fa-ewmy-saasfaprod1.fa.ocs.oraclecloud.com/hcmUI/CandidateExperience/en/sites/CX_1/job/1589', 'description' => null],
            ['company' => '1st Log AG', 'title' => 'Backend Entwickler (M/W/D) PHP/SHOPWARE', 'status' => 'Applied', 'created' => 45859.5584027778, 'applied' => 45859.5584027778, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4265420870', 'description' => null],
            ['company' => 'eightbit experts GmbH', 'title' => 'PHP Backend Developer (m/w/d) – TYPO3 & Symfony | Hamburg', 'status' => 'Rejected', 'created' => 45859.5577083333, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5577083333, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://www.linkedin.com/jobs/search/?currentJobId=4264125826', 'description' => null],
            ['company' => 'eppData', 'title' => 'PHP Symfony Software Developer', 'status' => 'Applied', 'created' => 45859.5567939815, 'applied' => 45859.5567939815, 'interviewed' => null, 'offered' => null, 'rejected' => null, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://eppdata.jobs.personio.de/job/1884419?display=en', 'description' => null],
            ['company' => 'Kaufland', 'title' => 'Senior PHP Engineer', 'status' => 'Rejected', 'created' => 45859.5556365741, 'applied' => 45859.5556365741, 'interviewed' => 45861.4257638889, 'offered' => null, 'rejected' => 45869.7427662037, 'location' => 'Köln', 'salary' => null, 'url' => 'https://join.com/companies/kaufland-e-commerce/14513194-senior-php-engineer-all-genders', 'description' => null],
            ['company' => 'Kettner-Edelmetalle', 'title' => 'Backend / Fullstack Developer - PHP & Laravel', 'status' => 'Rejected', 'created' => 45859.5546759259, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5546759259, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://jobs.kettner-edelmetalle.de/o/backend-fullstack-developer-php-laravel-2/c/new', 'description' => null],
            ['company' => 'Local Brand X GmbH', 'title' => 'Backend PHP-Developer (m/f/x)', 'status' => 'Rejected', 'created' => 45859.5537037037, 'applied' => 45859.5537152778, 'interviewed' => null, 'offered' => null, 'rejected' => 45863.5157638889, 'location' => 'Hamburg', 'salary' => null, 'url' => 'https://join.com/companies/local-brand-x/14490831-backend-php-developer-m-f-x-remote-freelance', 'description' => null],
            ['company' => 'Travix Media', 'title' => 'PHP-Entwickler', 'status' => 'Rejected', 'created' => 45859.5515162037, 'applied' => 45859.5515162037, 'interviewed' => null, 'offered' => null, 'rejected' => 45890.4208333333, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://www.travix-media.de/pdf/PHP-Entwickler_mwd_TravixMedia_2024.pdf', 'description' => null],
            ['company' => 'S-Kon Gruppe', 'title' => 'Backend-Entwickler', 'status' => 'Rejected', 'created' => 45859.5500810185, 'applied' => 45859.5500925926, 'interviewed' => 45868.7901157407, 'offered' => null, 'rejected' => 45890.6973611111, 'location' => 'Hamburg', 'salary' => 70000, 'url' => 'https://skon.softgarden.io/job/57960273/Backend-Entwickler-m-w-d-', 'description' => null],
            ['company' => 'Transfermarkt', 'title' => 'Backend Developer', 'status' => 'Rejected', 'created' => 45859.5449421296, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5449421296, 'location' => 'Hamburg', 'salary' => 70000, 'url' => '', 'description' => null],
            ['company' => 'My Dream Company', 'title' => 'My Dream Job Title', 'status' => 'Rejected', 'created' => 45859.5435416667, 'applied' => null, 'interviewed' => null, 'offered' => null, 'rejected' => 45859.5436921296, 'location' => '', 'salary' => null, 'url' => '', 'description' => null],
        ];
    }
}
