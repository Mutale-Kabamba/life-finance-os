<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\ExpenseCategory;
use App\Models\Profile;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class OnboardingWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Welcome to Life Finance OS';

    protected static string $view = 'filament.pages.onboarding-wizard';

    /**
     * Render full-screen with no sidebar/navigation — the wizard is the only
     * thing the user sees until onboarding is complete.
     */
    protected static string $layout = 'filament-panels::components.layout.simple';

    /**
     * Never show in navigation — it is reached only via the onboarding middleware.
     */
    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $profile = Auth::user()?->profile;

        $this->form->fill([
            'date_of_birth'  => $profile?->date_of_birth,
            'province'       => $profile?->province,
            'district'       => $profile?->district,
            'housing_type'   => $profile?->housing_type ?? 'renting',
            'marital_status' => $profile?->marital_status ?? 'single',
        ]);
    }

    public function getSubheading(): ?string
    {
        return 'Tell us what you want to track. Only the main entries are needed now — you can add more anytime.';
    }

    public function hasLogo(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getLayoutData(): array
    {
        return [
            'hasTopbar' => true,
            'maxWidth'  => MaxWidth::SevenExtraLarge,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    $this->demographicsStep(),
                    $this->modulesStep(),
                    $this->incomeStep(),
                    $this->expensesStep(),
                    $this->budgetStep(),
                    $this->savingsStep(),
                    $this->debtsStep(),
                    $this->receivablesStep(),
                    $this->businessStep(),
                    $this->familyStep(),
                    $this->investmentsStep(),
                ])
                    ->persistStepInQueryString('step')
                    ->submitAction($this->finishButton()),
            ])
            ->statePath('data');
    }

    protected function demographicsStep(): Step
    {
        return Step::make('About you')
            ->description('Your basic details')
            ->icon('heroicon-o-identification')
            ->columns(2)
            ->schema([
                DatePicker::make('date_of_birth')
                    ->label('Date of birth')
                    ->required()
                    ->maxDate(now()),
                Select::make('province')
                    ->label('Province')
                    ->options([
                        'Central' => 'Central', 'Copperbelt' => 'Copperbelt',
                        'Eastern' => 'Eastern', 'Luapula' => 'Luapula',
                        'Lusaka' => 'Lusaka', 'Muchinga' => 'Muchinga',
                        'Northern' => 'Northern', 'North-Western' => 'North-Western',
                        'Southern' => 'Southern', 'Western' => 'Western',
                    ])
                    ->searchable()
                    ->required(),
                TextInput::make('district')
                    ->label('District')
                    ->required()
                    ->maxLength(100),
                Select::make('housing_type')
                    ->label('Living situation')
                    ->options([
                        'own' => 'I own my home',
                        'renting' => 'I am renting',
                        'family' => 'Living with family',
                        'company' => 'Company-provided housing',
                    ])
                    ->native(false)
                    ->required(),
            ]);
    }

    protected function modulesStep(): Step
    {
        return Step::make('Your modules')
            ->description('What should we set up?')
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                Section::make('Choose what to track')
                    ->description('We will only show the tools you select. You can enable more later.')
                    ->columns(2)
                    ->schema([
                        Radio::make('marital_status')
                            ->label('Marital status')
                            ->options(['single' => 'Single', 'married' => 'Married'])
                            ->default('single')
                            ->required()
                            ->live(),
                        Toggle::make('has_children')
                            ->label('I have children')
                            ->default(false)
                            ->live(),
                        Toggle::make('has_business')
                            ->label('I own a business')
                            ->default(false)
                            ->live(),
                        Toggle::make('has_investments')
                            ->label('I have investments')
                            ->default(false)
                            ->live(),
                    ]),
            ]);
    }

    protected function incomeStep(): Step
    {
        return Step::make('Income')
            ->description('Your main income')
            ->icon('heroicon-o-banknotes')
            ->schema([
                Section::make('Main income source')
                    ->description('Add your primary income. Other sources can be added later.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('income.name')
                            ->label('Source name')
                            ->placeholder('e.g. Salary — ABC Ltd')
                            ->maxLength(255),
                        Select::make('income.type')
                            ->label('Type')
                            ->options([
                                'salary' => 'Salary', 'business' => 'Business',
                                'freelancing' => 'Freelancing', 'farming' => 'Farming',
                                'rental' => 'Rental', 'investment' => 'Investment',
                                'side_hustle' => 'Side hustle', 'pension' => 'Pension',
                                'other' => 'Other',
                            ])
                            ->default('salary')
                            ->native(false),
                        TextInput::make('income.amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0),
                        Select::make('income.frequency')
                            ->label('Frequency')
                            ->options($this->frequencyOptions())
                            ->default('monthly')
                            ->native(false),
                    ]),
            ]);
    }

    protected function expensesStep(): Step
    {
        return Step::make('Expenses')
            ->description('A recurring expense')
            ->icon('heroicon-o-receipt-percent')
            ->schema([
                Section::make('Main recurring expense')
                    ->description('Add your biggest recurring cost. Others can be added later.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('expense.name')
                            ->label('Expense name')
                            ->placeholder('e.g. Rent')
                            ->maxLength(255),
                        Select::make('expense.expense_category_id')
                            ->label('Category')
                            ->options(fn (): array => ExpenseCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->native(false),
                        TextInput::make('expense.amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0),
                        Select::make('expense.frequency')
                            ->label('Frequency')
                            ->options(['one_time' => 'One time'] + $this->frequencyOptions())
                            ->default('monthly')
                            ->native(false),
                    ]),
            ]);
    }

    protected function budgetStep(): Step
    {
        return Step::make('Budget')
            ->description('Your spending plan')
            ->icon('heroicon-o-calculator')
            ->schema([
                Section::make('Main budget')
                    ->description('A simple plan to start with. Add budget items later.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('budget.name')
                            ->label('Budget name')
                            ->default('Monthly Budget')
                            ->maxLength(255),
                        Select::make('budget.period')
                            ->label('Period')
                            ->options([
                                'weekly' => 'Weekly', 'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly', 'annual' => 'Annual',
                            ])
                            ->default('monthly')
                            ->native(false),
                        TextInput::make('budget.total_budgeted')
                            ->label('Planned spend')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0),
                    ]),
            ]);
    }

    protected function savingsStep(): Step
    {
        return Step::make('Savings')
            ->description('A savings goal')
            ->icon('heroicon-o-flag')
            ->schema([
                Section::make('Main savings goal')
                    ->description('Set one goal now. Add more goals later.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('savings.name')
                            ->label('Goal name')
                            ->placeholder('e.g. Emergency Fund')
                            ->maxLength(255),
                        Select::make('savings.category')
                            ->label('Category')
                            ->options([
                                'emergency_fund' => 'Emergency fund', 'school_fees' => 'School fees',
                                'wedding' => 'Wedding', 'vehicle' => 'Vehicle', 'house' => 'House',
                                'land' => 'Land', 'business_capital' => 'Business capital',
                                'holiday' => 'Holiday', 'retirement' => 'Retirement', 'other' => 'Other',
                            ])
                            ->default('emergency_fund')
                            ->native(false),
                        TextInput::make('savings.target_amount')
                            ->label('Target amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0),
                        DatePicker::make('savings.target_date')
                            ->label('Target date')
                            ->minDate(now()),
                        TextInput::make('savings.monthly_contribution')
                            ->label('Monthly contribution')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0),
                    ]),
            ]);
    }

    protected function debtsStep(): Step
    {
        return Step::make('Debts')
            ->description('Who you owe')
            ->icon('heroicon-o-arrow-trending-down')
            ->schema([
                Repeater::make('debts')
                    ->label('People or institutions you owe')
                    ->addActionLabel('Add a debt')
                    ->columns(2)
                    ->default([])
                    ->schema([
                        TextInput::make('creditor_name')
                            ->label('Creditor')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'bank_loan' => 'Bank loan', 'mobile_loan' => 'Mobile loan',
                                'mortgage' => 'Mortgage', 'vehicle_loan' => 'Vehicle loan',
                                'personal_loan' => 'Personal loan', 'hire_purchase' => 'Hire purchase',
                                'credit_card' => 'Credit card', 'student_loan' => 'Student loan',
                                'other' => 'Other',
                            ])
                            ->default('personal_loan')
                            ->native(false),
                        TextInput::make('outstanding_balance')
                            ->label('Outstanding balance')
                            ->numeric()
                            ->prefix('ZMW')
                            ->required()
                            ->minValue(0),
                        TextInput::make('monthly_installment')
                            ->label('Monthly installment')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->minValue(0),
                    ]),
            ]);
    }

    protected function receivablesStep(): Step
    {
        return Step::make('Owed to you')
            ->description('Who owes you')
            ->icon('heroicon-o-arrow-trending-up')
            ->schema([
                Repeater::make('receivables')
                    ->label('People who owe you money')
                    ->addActionLabel('Add a receivable')
                    ->columns(2)
                    ->default([])
                    ->schema([
                        TextInput::make('name')
                            ->label('Person or entity')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->required()
                            ->minValue(0),
                        DatePicker::make('due_date')
                            ->label('Expected by'),
                        TextInput::make('notes')
                            ->label('Notes')
                            ->maxLength(255),
                    ]),
            ]);
    }

    protected function businessStep(): Step
    {
        return Step::make('Your business')
            ->description('Business profile')
            ->icon('heroicon-o-building-office-2')
            ->visible(fn (Get $get): bool => (bool) $get('has_business'))
            ->schema([
                Section::make('Business profile')
                    ->columns(2)
                    ->schema([
                        TextInput::make('business.name')
                            ->label('Business name')
                            ->required(fn (Get $get): bool => (bool) $get('has_business'))
                            ->maxLength(255),
                        Select::make('business.type')
                            ->label('Type')
                            ->options([
                                'sole_trader' => 'Sole Trader', 'partnership' => 'Partnership',
                                'private_limited' => 'Private Limited (Ltd)', 'public_limited' => 'Public Limited (PLC)',
                                'cooperative' => 'Cooperative', 'ngo' => 'NGO / Non-Profit', 'other' => 'Other',
                            ])
                            ->default('sole_trader')
                            ->native(false),
                        TextInput::make('business.industry')
                            ->label('Industry')
                            ->maxLength(100),
                        TextInput::make('business.currency')
                            ->label('Currency')
                            ->default('ZMW')
                            ->maxLength(5),
                    ]),
            ]);
    }

    protected function familyStep(): Step
    {
        return Step::make('Your family')
            ->description('Spouse & children')
            ->icon('heroicon-o-users')
            ->visible(fn (Get $get): bool => $get('marital_status') === 'married' || (bool) $get('has_children'))
            ->schema([
                Section::make('Spouse')
                    ->visible(fn (Get $get): bool => $get('marital_status') === 'married')
                    ->columns(2)
                    ->schema([
                        TextInput::make('spouse.first_name')
                            ->label('First name')
                            ->required(fn (Get $get): bool => $get('marital_status') === 'married')
                            ->maxLength(100),
                        TextInput::make('spouse.last_name')
                            ->label('Last name')
                            ->maxLength(100),
                        Select::make('spouse.employment_status')
                            ->label('Employment')
                            ->options([
                                'employed' => 'Employed', 'self_employed' => 'Self-employed',
                                'unemployed' => 'Unemployed', 'student' => 'Student', 'homemaker' => 'Homemaker',
                            ])
                            ->default('employed')
                            ->native(false),
                        TextInput::make('spouse.monthly_income')
                            ->label('Monthly income')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0),
                        DatePicker::make('spouse.marriage_date')
                            ->label('Marriage date'),
                    ]),
                Section::make('Children')
                    ->visible(fn (Get $get): bool => (bool) $get('has_children'))
                    ->schema([
                        Repeater::make('children')
                            ->label('Your children')
                            ->addActionLabel('Add child')
                            ->columns(2)
                            ->default([])
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('First name')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('last_name')
                                    ->label('Last name')
                                    ->maxLength(100),
                                DatePicker::make('date_of_birth')
                                    ->label('Date of birth')
                                    ->required()
                                    ->maxDate(now()),
                                TextInput::make('school_name')
                                    ->label('School')
                                    ->maxLength(255),
                                TextInput::make('annual_school_fees')
                                    ->label('Annual school fees')
                                    ->numeric()
                                    ->prefix('ZMW')
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }

    protected function investmentsStep(): Step
    {
        return Step::make('Investments')
            ->description('Your investments')
            ->icon('heroicon-o-chart-bar')
            ->visible(fn (Get $get): bool => (bool) $get('has_investments'))
            ->schema([
                Section::make('Main investment')
                    ->columns(2)
                    ->schema([
                        TextInput::make('investment.name')
                            ->label('Investment name')
                            ->required(fn (Get $get): bool => (bool) $get('has_investments'))
                            ->maxLength(255),
                        Select::make('investment.type')
                            ->label('Type')
                            ->options([
                                'stocks' => 'Stocks', 'bonds' => 'Bonds', 'treasury_bills' => 'Treasury bills',
                                'fixed_deposit' => 'Fixed deposit', 'unit_trust' => 'Unit trust',
                                'mutual_fund' => 'Mutual fund', 'real_estate' => 'Real estate',
                                'cryptocurrency' => 'Cryptocurrency', 'business' => 'Business',
                                'farming' => 'Farming', 'other' => 'Other',
                            ])
                            ->default('other')
                            ->native(false),
                        TextInput::make('investment.institution')
                            ->label('Institution')
                            ->maxLength(255),
                        TextInput::make('investment.initial_amount')
                            ->label('Amount invested')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0),
                        TextInput::make('investment.current_value')
                            ->label('Current value')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0),
                        DatePicker::make('investment.start_date')
                            ->label('Start date')
                            ->default(now()),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function frequencyOptions(): array
    {
        return [
            'daily' => 'Daily', 'weekly' => 'Weekly', 'bi_weekly' => 'Bi-weekly',
            'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annually' => 'Annually',
        ];
    }

    protected function finishButton(): HtmlString
    {
        return new HtmlString(<<<'HTML'
            <button
                type="submit"
                class="fi-btn fi-btn-size-md relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 rounded-lg gap-1.5 px-3.5 py-2.5 text-sm inline-grid bg-primary-600 text-white hover:bg-primary-500"
            >
                Finish setup
            </button>
        HTML);
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $user  = Auth::user();

        $featureRegistry = [
            'has_business'    => (bool) ($state['has_business'] ?? false),
            'has_spouse'      => ($state['marital_status'] ?? 'single') === 'married',
            'has_children'    => (bool) ($state['has_children'] ?? false),
            'has_investments' => (bool) ($state['has_investments'] ?? false),
        ];

        $activeModules = collect([
            'personal'    => true,
            'family'      => $featureRegistry['has_spouse'] || $featureRegistry['has_children'],
            'business'    => $featureRegistry['has_business'],
            'investments' => $featureRegistry['has_investments'],
        ])->filter()->keys()->all();

        DB::transaction(function () use ($state, $user, $featureRegistry, $activeModules): void {
            $profile = $user->profile ?? new Profile(['user_id' => $user->id]);

            // profiles.first_name / last_name are required — derive from the
            // registered user name when the profile does not yet have them.
            $nameParts = preg_split('/\s+/', trim((string) ($user->name ?? '')), 2);

            $profile->fill([
                'first_name'           => $profile->first_name ?: ($nameParts[0] ?? 'User'),
                'last_name'            => $profile->last_name ?: ($nameParts[1] ?? ''),
                'date_of_birth'        => $state['date_of_birth'] ?? null,
                'province'             => $state['province'] ?? null,
                'district'             => $state['district'] ?? null,
                'housing_type'         => $state['housing_type'] ?? 'renting',
                'marital_status'       => $state['marital_status'] ?? 'single',
                'feature_registry'     => $featureRegistry,
                'active_modules'       => $activeModules,
                'onboarding_completed' => true,
            ]);
            $profile->user_id = $user->id;
            $profile->save();

            if (filled($state['income']['amount'] ?? null)) {
                $user->incomeSources()->create([
                    'name'       => $state['income']['name'] ?: 'Main income',
                    'type'       => $state['income']['type'] ?? 'salary',
                    'amount'     => $state['income']['amount'],
                    'frequency'  => $state['income']['frequency'] ?? 'monthly',
                    'start_date' => now(),
                    'is_active'  => true,
                ]);
            }

            if (filled($state['expense']['amount'] ?? null)) {
                $categoryId = $state['expense']['expense_category_id']
                    ?? ExpenseCategory::query()
                        ->firstOrCreate(['slug' => 'other'], ['name' => 'Other', 'is_system' => true])
                        ->getKey();

                $user->expenses()->create([
                    'expense_category_id' => $categoryId,
                    'name'                => $state['expense']['name'] ?: 'Recurring expense',
                    'amount'              => $state['expense']['amount'],
                    'expense_date'        => now(),
                    'frequency'           => $state['expense']['frequency'] ?? 'monthly',
                    'is_recurring'        => true,
                ]);
            }

            if (filled($state['budget']['total_budgeted'] ?? null) || filled($state['budget']['name'] ?? null)) {
                $period = $state['budget']['period'] ?? 'monthly';
                [$start, $end] = $this->periodRange($period);

                $user->budgets()->create([
                    'name'           => $state['budget']['name'] ?: 'Monthly Budget',
                    'period'         => $period,
                    'start_date'     => $start,
                    'end_date'       => $end,
                    'total_income'   => $state['income']['amount'] ?? 0,
                    'total_budgeted' => $state['budget']['total_budgeted'] ?? 0,
                    'status'         => 'active',
                ]);
            }

            if (filled($state['savings']['target_amount'] ?? null)) {
                $user->savingsGoals()->create([
                    'name'                 => $state['savings']['name'] ?: 'Savings goal',
                    'category'             => $state['savings']['category'] ?? 'other',
                    'target_amount'        => $state['savings']['target_amount'],
                    'current_amount'       => 0,
                    'target_date'          => $state['savings']['target_date'] ?? null,
                    'monthly_contribution' => $state['savings']['monthly_contribution'] ?? 0,
                    'status'               => 'active',
                ]);
            }

            foreach ($state['debts'] ?? [] as $debt) {
                if (blank($debt['creditor_name'] ?? null)) {
                    continue;
                }

                $balance = (float) ($debt['outstanding_balance'] ?? 0);

                $user->debts()->create([
                    'creditor_name'       => $debt['creditor_name'],
                    'type'                => $debt['type'] ?? 'personal_loan',
                    'original_amount'     => $balance,
                    'outstanding_balance' => $balance,
                    'monthly_installment' => $debt['monthly_installment'] ?? 0,
                    'status'              => 'active',
                ]);
            }

            foreach ($state['receivables'] ?? [] as $receivable) {
                if (blank($receivable['name'] ?? null)) {
                    continue;
                }

                $user->receivables()->create([
                    'debtor_name' => $receivable['name'],
                    'amount'      => (float) ($receivable['amount'] ?? 0),
                    'due_date'    => $receivable['due_date'] ?? null,
                    'notes'       => $receivable['notes'] ?? null,
                    'status'      => 'pending',
                ]);
            }

            if ($featureRegistry['has_business'] && filled($state['business']['name'] ?? null)) {
                $user->businesses()->create([
                    'name'      => $state['business']['name'],
                    'type'      => $state['business']['type'] ?? 'sole_trader',
                    'industry'  => $state['business']['industry'] ?? null,
                    'currency'  => $state['business']['currency'] ?? 'ZMW',
                    'is_active' => true,
                ]);
            }

            if ($featureRegistry['has_spouse'] || $featureRegistry['has_children']) {
                $family = $user->family ?? $user->family()->create([
                    'family_name' => trim(($user->name ?? 'My') . ' Family'),
                ]);

                if ($featureRegistry['has_spouse'] && filled($state['spouse']['first_name'] ?? null)) {
                    $family->spouse()->create([
                        'user_id'           => $user->id,
                        'first_name'        => $state['spouse']['first_name'],
                        'last_name'         => $state['spouse']['last_name'] ?? '',
                        'employment_status' => $state['spouse']['employment_status'] ?? 'employed',
                        'monthly_income'    => $state['spouse']['monthly_income'] ?? 0,
                        'marriage_date'     => $state['spouse']['marriage_date'] ?? null,
                    ]);
                }

                if ($featureRegistry['has_children']) {
                    foreach ($state['children'] ?? [] as $child) {
                        if (blank($child['first_name'] ?? null)) {
                            continue;
                        }

                        $family->children()->create([
                            'user_id'            => $user->id,
                            'first_name'         => $child['first_name'],
                            'last_name'          => $child['last_name'] ?? '',
                            'date_of_birth'      => $child['date_of_birth'] ?? null,
                            'school_name'        => $child['school_name'] ?? null,
                            'annual_school_fees' => $child['annual_school_fees'] ?? 0,
                        ]);
                    }
                }
            }

            if ($featureRegistry['has_investments'] && filled($state['investment']['name'] ?? null)) {
                $user->investments()->create([
                    'name'           => $state['investment']['name'],
                    'type'           => $state['investment']['type'] ?? 'other',
                    'institution'    => $state['investment']['institution'] ?? null,
                    'initial_amount' => $state['investment']['initial_amount'] ?? 0,
                    'current_value'  => $state['investment']['current_value'] ?: ($state['investment']['initial_amount'] ?? 0),
                    'start_date'     => $state['investment']['start_date'] ?? now(),
                    'status'         => 'active',
                ]);
            }
        });

        Notification::make()
            ->title('Welcome aboard! Your account is ready.')
            ->success()
            ->send();

        $this->redirect(Filament::getPanel('app')->getUrl());
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function periodRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'weekly'    => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'quarterly' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'annual'    => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default     => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }
}
