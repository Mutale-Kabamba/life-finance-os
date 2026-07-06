<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\ExpenseCategory;
use App\Models\Profile;
use App\Support\ExpenseCategoryDefaults;
use App\Support\ZambiaReferenceData;
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
        ExpenseCategoryDefaults::ensure();

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
                    ->options(ZambiaReferenceData::provinceOptions())
                    ->live()
                    ->searchable()
                    ->native(false)
                    ->required(),
                Select::make('district')
                    ->label('District')
                    ->options(function (Get $get): array {
                        $options = ZambiaReferenceData::districtOptions((string) $get('province'));
                        $current = trim((string) $get('district'));
                        if ($current !== '' && ! array_key_exists($current, $options)) {
                            $options[$current] = $current;
                        }

                        return $options;
                    })
                    ->disabled(fn (Get $get): bool => blank($get('province')))
                    ->searchable()
                    ->native(false)
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('District name')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->createOptionUsing(fn (array $data): string => trim((string) ($data['name'] ?? '')))
                    ->required(),
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
            ->description('Stable and predictable income sources')
            ->icon('heroicon-o-banknotes')
            ->schema([
                Section::make('Expected recurring income')
                    ->description('Add all reliable income streams used for budgeting and forecasting.')
                    ->schema([
                        Repeater::make('income_sources')
                            ->label('Income sources')
                            ->addActionLabel('Add income source')
                            ->default([])
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Source name')
                                    ->placeholder('e.g. Salary - ABC Ltd')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'salary' => 'Salary', 'business' => 'Business',
                                        'freelancing' => 'Freelancing', 'farming' => 'Farming',
                                        'rental' => 'Rental', 'investment' => 'Investment',
                                        'allowance' => 'Allowance', 'pension' => 'Pension',
                                        'side_hustle' => 'Side hustle', 'other' => 'Other',
                                    ])
                                    ->default('salary')
                                    ->native(false),
                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('ZMW')
                                    ->required()
                                    ->minValue(0),
                                Select::make('frequency')
                                    ->label('Frequency')
                                    ->options($this->frequencyOptions())
                                    ->default('monthly')
                                    ->native(false),
                            ]),
                    ]),
            ]);
    }

    protected function expensesStep(): Step
    {
        return Step::make('Expenses')
            ->description('Recurring and mandatory obligations')
            ->icon('heroicon-o-receipt-percent')
            ->schema([
                Section::make('Recurring commitments')
                    ->description('Register rent, utilities, subscriptions, school fees, transport, and other commitments.')
                    ->schema([
                        Repeater::make('recurring_expenses')
                            ->label('Recurring expenses')
                            ->addActionLabel('Add recurring expense')
                            ->default([])
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Expense name')
                                    ->placeholder('e.g. Rent')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('expense_category_id')
                                    ->label('Category')
                                    ->options(fn (): array => ExpenseCategoryDefaults::options())
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('New category name')
                                            ->required()
                                            ->maxLength(100),
                                    ])
                                    ->createOptionUsing(fn (array $data): int => ExpenseCategoryDefaults::createFromName((string) ($data['name'] ?? '')))
                                    ->searchable()
                                    ->native(false)
                                    ->required(),
                                TextInput::make('amount')
                                    ->label('Amount')
                                    ->numeric()
                                    ->prefix('ZMW')
                                    ->required()
                                    ->minValue(0),
                                Select::make('frequency')
                                    ->label('Frequency')
                                    ->options($this->frequencyOptions())
                                    ->default('monthly')
                                    ->native(false),
                                Toggle::make('is_mandatory')
                                    ->label('Mandatory commitment')
                                    ->default(true),
                            ]),
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
                            ->live()
                            ->native(false),
                        TextInput::make('outstanding_balance')
                            ->label(fn (Get $get): string => $get('type') === 'hire_purchase' ? 'Cash price (item value)' : 'Loan amount (principal)')
                            ->numeric()
                            ->prefix('ZMW')
                            ->required()
                            ->minValue(0)
                            ->helperText(fn (Get $get): string => $get('type') === 'hire_purchase'
                                ? 'For hire purchase, enter the item cash price. Use deposit and term details below.'
                                : 'For standard debt products, enter the principal borrowed amount.')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, Get $get): void {
                                $this->syncDebtRepeaterLoanAmounts($set, $get, 'outstanding_balance');
                            }),
                        TextInput::make('details.item_name')
                            ->label('Item being purchased')
                            ->placeholder('e.g. Toyota Axio, fridge, solar kit')
                            ->visible(fn (Get $get): bool => $get('type') === 'hire_purchase')
                            ->required(fn (Get $get): bool => $get('type') === 'hire_purchase')
                            ->maxLength(255),
                        TextInput::make('details.deposit_amount')
                            ->label('Deposit paid')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['hire_purchase', 'vehicle_loan', 'mortgage'], true))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, Get $get): void {
                                $this->syncDebtRepeaterLoanAmounts($set, $get, 'details.deposit_amount');
                            }),
                        TextInput::make('monthly_installment')
                            ->label('Installment amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->minValue(0)
                            ->readOnly(fn (Get $get): bool => $get('type') === 'hire_purchase')
                            ->helperText(function (Get $get): string {
                                if ($get('type') !== 'hire_purchase') {
                                    return 'Amount paid per repayment cycle.';
                                }

                                $suggested = $this->toFloat($get('details.suggested_installment'));
                                $remainingTerm = max((int) $this->toFloat($get('details.remaining_term_months')), 0);
                                $financed = $this->toFloat($get('details.financed_amount'));

                                if ($suggested > 0 && $remainingTerm > 0) {
                                    return 'Suggested plan: ZMW ' . number_format($suggested, 2) . ' per month for about '
                                        . $remainingTerm . ' month(s) on ZMW ' . number_format($financed, 2) . ' remaining financed amount.';
                                }

                                return 'Set term + total repayment to get an automatic monthly plan suggestion.';
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, Get $get): void {
                                $this->syncDebtRepeaterLoanAmounts($set, $get, 'monthly_installment');
                            }),
                        TextInput::make('details.term_months')
                            ->label('Repayment term (months)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->visible(fn (Get $get): bool => in_array($get('type'), ['hire_purchase', 'vehicle_loan', 'mortgage', 'bank_loan', 'personal_loan', 'student_loan'], true))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, Get $get): void {
                                $this->syncDebtRepeaterLoanAmounts($set, $get, 'details.term_months');
                            }),
                        Select::make('repayment_frequency')
                            ->label('Repayment frequency')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'bi_weekly' => 'Bi-weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->placeholder('Flexible / not fixed')
                            ->native(false),
                        Select::make('details.mobile_provider')
                            ->label('Mobile lender')
                            ->options([
                                'airtel_money' => 'Airtel Money',
                                'mtn_momo' => 'MTN MoMo',
                                'zamtel_kwacha' => 'Zamtel Kwacha',
                                'other' => 'Other',
                            ])
                            ->visible(fn (Get $get): bool => $get('type') === 'mobile_loan')
                            ->native(false),
                        TextInput::make('interest_rate')
                            ->label('Interest rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => $get('type') === 'personal_loan')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, Get $get): void {
                                $this->syncDebtRepeaterLoanAmounts($set, $get, 'interest_rate');
                            }),
                        TextInput::make('total_repayment_amount')
                            ->label(fn (Get $get): string => $this->isPurchaseDebtType((string) $get('type')) ? 'Hire purchase price' : 'Total repayment amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, Get $get): void {
                                $this->syncDebtRepeaterLoanAmounts($set, $get, 'total_repayment_amount');
                            }),
                        DatePicker::make('start_date')
                            ->label(fn (Get $get): string => $this->isPurchaseDebtType((string) $get('type')) ? 'Date purchased' : 'Date borrowed'),
                        DatePicker::make('due_date')
                            ->label('Expected repayment date'),
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
                        Select::make('business.industry')
                            ->label('Industry')
                            ->options(function (Get $get): array {
                                $options = ZambiaReferenceData::businessIndustryOptions();
                                $current = trim((string) $get('business.industry'));
                                if ($current !== '' && ! array_key_exists($current, $options)) {
                                    $options[$current] = $current;
                                }

                                return $options;
                            })
                            ->searchable()
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Industry name')
                                    ->required()
                                    ->maxLength(100),
                            ])
                            ->createOptionUsing(fn (array $data): string => trim((string) ($data['name'] ?? ''))),
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
                                'stocks' => 'Listed Shares (LuSE / Global)',
                                'bonds' => 'Government / Corporate Bonds',
                                'treasury_bills' => 'Treasury Bills (BoZ)',
                                'fixed_deposit' => 'Fixed Deposit',
                                'unit_trust' => 'Unit Trust / CIS',
                                'mutual_fund' => 'Mutual Fund',
                                'real_estate' => 'Real Estate',
                                'cryptocurrency' => 'Cryptocurrency',
                                'business' => 'Business Equity',
                                'farming' => 'Farming / Livestock',
                                'other' => 'Other',
                            ])
                            ->default('other')
                            ->live()
                            ->native(false),
                        Select::make('investment.details.subtype')
                            ->label('Subtype')
                            ->options(fn (Get $get): array => $this->investmentSubtypeOptions((string) $get('investment.type')))
                            ->searchable()
                            ->native(false),
                        TextInput::make('investment.institution')
                            ->label('Institution')
                            ->maxLength(255),
                        TextInput::make('investment.initial_amount')
                            ->label('Amount invested')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->live(onBlur: true),
                        TextInput::make('investment.details.rate_percent')
                            ->label('Annual return rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->visible(fn (Get $get): bool => in_array($get('investment.type'), ['treasury_bills', 'bonds', 'fixed_deposit'], true)),
                        TextInput::make('investment.details.tenor_months')
                            ->label('Tenor (months)')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->visible(fn (Get $get): bool => in_array($get('investment.type'), ['treasury_bills', 'bonds', 'fixed_deposit'], true)),
                        TextInput::make('investment.details.units')
                            ->label('Units / shares held')
                            ->numeric()
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => in_array($get('investment.type'), ['stocks', 'unit_trust', 'mutual_fund', 'cryptocurrency'], true)),
                        TextInput::make('investment.details.current_unit_price')
                            ->label('Current unit/share price')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => in_array($get('investment.type'), ['stocks', 'unit_trust', 'mutual_fund', 'cryptocurrency'], true)),
                        TextInput::make('investment.details.annual_net_income')
                            ->label('Estimated annual net income')
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0)
                            ->visible(fn (Get $get): bool => in_array($get('investment.type'), ['real_estate', 'business', 'farming'], true)),
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

            $incomeSources = $state['income_sources'] ?? [];
            if (empty($incomeSources) && filled($state['income']['amount'] ?? null)) {
                $incomeSources = [[
                    'name' => $state['income']['name'] ?? 'Main income',
                    'type' => $state['income']['type'] ?? 'salary',
                    'amount' => $state['income']['amount'],
                    'frequency' => $state['income']['frequency'] ?? 'monthly',
                ]];
            }

            foreach ($incomeSources as $incomeSource) {
                if (blank($incomeSource['name'] ?? null) || ! filled($incomeSource['amount'] ?? null)) {
                    continue;
                }

                $type = (string) ($incomeSource['type'] ?? 'other');
                if (! in_array($type, ['salary', 'business', 'freelancing', 'farming', 'rental', 'investment', 'side_hustle', 'pension', 'other'], true)) {
                    $type = 'other';
                }

                $user->incomeSources()->create([
                    'name'       => $incomeSource['name'],
                    'type'       => $type,
                    'amount'     => $incomeSource['amount'],
                    'frequency'  => $incomeSource['frequency'] ?? 'monthly',
                    'start_date' => now(),
                    'is_active'  => true,
                ]);
            }

            $recurringExpenses = $state['recurring_expenses'] ?? [];
            if (empty($recurringExpenses) && filled($state['expense']['amount'] ?? null)) {
                $recurringExpenses = [[
                    'expense_category_id' => $state['expense']['expense_category_id'] ?? null,
                    'name' => $state['expense']['name'] ?? 'Recurring expense',
                    'amount' => $state['expense']['amount'],
                    'frequency' => $state['expense']['frequency'] ?? 'monthly',
                    'is_mandatory' => true,
                ]];
            }

            foreach ($recurringExpenses as $expense) {
                if (blank($expense['name'] ?? null) || ! filled($expense['amount'] ?? null)) {
                    continue;
                }

                $categoryId = $expense['expense_category_id']
                    ?? ExpenseCategory::query()
                        ->firstOrCreate(['slug' => 'other'], ['name' => 'Other', 'is_system' => true])
                        ->getKey();

                if (! $categoryId || ! ExpenseCategory::query()->whereKey($categoryId)->exists()) {
                    $categoryId = ExpenseCategoryDefaults::defaultCategoryId();
                }

                $user->expenses()->create([
                    'expense_category_id' => $categoryId,
                    'name'                => $expense['name'],
                    'amount'              => $expense['amount'],
                    'expense_date'        => now(),
                    'frequency'           => $expense['frequency'] ?? 'monthly',
                    'is_recurring'        => true,
                    'is_mandatory'        => (bool) ($expense['is_mandatory'] ?? true),
                ]);
            }

            $monthlyExpectedIncome = collect($incomeSources)
                ->sum(fn (array $incomeSource): float => $this->toMonthlyAmount(
                    (float) ($incomeSource['amount'] ?? 0),
                    (string) ($incomeSource['frequency'] ?? 'monthly')
                ));

            if (filled($state['budget']['total_budgeted'] ?? null) || filled($state['budget']['name'] ?? null)) {
                $period = $state['budget']['period'] ?? 'monthly';
                [$start, $end] = $this->periodRange($period);

                $user->budgets()->create([
                    'name'           => $state['budget']['name'] ?: 'Monthly Budget',
                    'period'         => $period,
                    'start_date'     => $start,
                    'end_date'       => $end,
                    'total_income'   => $monthlyExpectedIncome,
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

                $type = (string) ($debt['type'] ?? 'personal_loan');
                $principal = (float) ($debt['outstanding_balance'] ?? 0);
                $monthlyInstallment = (float) ($debt['monthly_installment'] ?? 0);
                $enteredTotal = (float) ($debt['total_repayment_amount'] ?? 0);
                $termMonths = (int) ($debt['details']['term_months'] ?? 0);
                $deposit = (float) ($debt['details']['deposit_amount'] ?? 0);
                $interestRate = 0.0;

                $totalRepayment = $enteredTotal;
                $outstanding = $principal;

                if ($type === 'hire_purchase') {
                    if ($totalRepayment <= 0 && $monthlyInstallment > 0 && $termMonths > 0) {
                        $totalRepayment = ($monthlyInstallment * $termMonths) + $deposit;
                    }

                    if ($totalRepayment <= 0) {
                        $totalRepayment = $principal;
                    }

                    $outstanding = max($totalRepayment - $deposit, 0);

                    $financedCashPrice = max($principal - $deposit, 0);
                    if ($financedCashPrice > 0) {
                        $interestRate = round(max((((($totalRepayment - $deposit) - $financedCashPrice) / $financedCashPrice) * 100), 0), 2);
                    }

                    $suggestedInstallment = 0.0;
                    if ($termMonths > 0 && $outstanding > 0) {
                        $suggestedInstallment = round($outstanding / $termMonths, 2);
                    }
                } else {
                    if ($type === 'personal_loan') {
                        if ($totalRepayment <= 0 && $interestRate > 0) {
                            $totalRepayment = round($principal * (1 + ($interestRate / 100)), 2);
                        }
                    }

                    if ($totalRepayment <= 0) {
                        $totalRepayment = $principal;
                    }

                    $suggestedInstallment = 0.0;

                    if ($principal > 0 && $totalRepayment > 0) {
                        $interestRate = round(max((($totalRepayment - $principal) / $principal) * 100, 0), 2);
                    }
                }

                $details = array_filter([
                    'item_name' => $debt['details']['item_name'] ?? null,
                    'deposit_amount' => $deposit > 0 ? $deposit : null,
                    'term_months' => $termMonths > 0 ? $termMonths : null,
                    'financed_amount' => $type === 'hire_purchase' ? round($outstanding, 2) : null,
                    'suggested_installment' => $type === 'hire_purchase' && $suggestedInstallment > 0 ? $suggestedInstallment : null,
                    'remaining_term_months' => $type === 'hire_purchase' && $termMonths > 0 ? $termMonths : null,
                    'mobile_provider' => $debt['details']['mobile_provider'] ?? null,
                ], fn ($value) => $value !== null && $value !== '');

                $user->debts()->create([
                    'creditor_name'       => $debt['creditor_name'],
                    'type'                => $type,
                    'original_amount'     => $principal,
                    'outstanding_balance' => $outstanding,
                    'monthly_installment' => $monthlyInstallment,
                    'repayment_frequency' => $debt['repayment_frequency'] ?? null,
                    'interest_rate'       => $interestRate,
                    'total_repayment_amount' => $totalRepayment > 0 ? $totalRepayment : null,
                    'start_date'          => $debt['start_date'] ?? null,
                    'due_date'            => $debt['due_date'] ?? null,
                    'status'              => 'active',
                    'details'             => $details ?: null,
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
                $investmentType = (string) ($state['investment']['type'] ?? 'other');
                $initialAmount = (float) ($state['investment']['initial_amount'] ?? 0);
                $enteredCurrentValue = (float) ($state['investment']['current_value'] ?? 0);
                $details = $state['investment']['details'] ?? [];

                $currentValue = $enteredCurrentValue;

                if ($currentValue <= 0) {
                    if (in_array($investmentType, ['treasury_bills', 'bonds', 'fixed_deposit'], true)) {
                        $rate = (float) ($details['rate_percent'] ?? 0);
                        $tenorMonths = max((int) ($details['tenor_months'] ?? 0), 0);
                        if ($rate > 0 && $tenorMonths > 0) {
                            $currentValue = round($initialAmount * (1 + (($rate / 100) * ($tenorMonths / 12))), 2);
                        }
                    } elseif (in_array($investmentType, ['stocks', 'unit_trust', 'mutual_fund', 'cryptocurrency'], true)) {
                        $units = (float) ($details['units'] ?? 0);
                        $currentUnitPrice = (float) ($details['current_unit_price'] ?? 0);
                        if ($units > 0 && $currentUnitPrice > 0) {
                            $currentValue = round($units * $currentUnitPrice, 2);
                        }
                    }
                }

                if ($currentValue <= 0) {
                    $currentValue = $initialAmount;
                }

                $expectedReturnRate = 0.0;
                if ($initialAmount > 0) {
                    if (in_array($investmentType, ['real_estate', 'business', 'farming'], true) && (float) ($details['annual_net_income'] ?? 0) > 0) {
                        $expectedReturnRate = round((((float) $details['annual_net_income']) / $initialAmount) * 100, 2);
                    } else {
                        $expectedReturnRate = round((($currentValue - $initialAmount) / $initialAmount) * 100, 2);
                    }
                }

                $user->investments()->create([
                    'name'           => $state['investment']['name'],
                    'type'           => $investmentType,
                    'institution'    => $state['investment']['institution'] ?? null,
                    'initial_amount' => $initialAmount,
                    'current_value'  => $currentValue,
                    'expected_return_rate' => $expectedReturnRate,
                    'start_date'     => $state['investment']['start_date'] ?? now(),
                    'status'         => 'active',
                    'details'        => ! empty($details) ? $details : null,
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

    private function syncDebtRepeaterLoanAmounts(\Filament\Forms\Set $set, Get $get, string $changedField): void
    {
        $type = (string) ($get('type') ?? 'personal_loan');
        $principal = $this->toFloat($get('outstanding_balance'));
        if ($principal <= 0) {
            return;
        }

        $deposit = $this->toFloat($get('details.deposit_amount'));
        $termMonths = max((int) $this->toFloat($get('details.term_months')), 0);
        $installment = $this->toFloat($get('monthly_installment'));
        $interest = $this->toFloat($get('interest_rate'));
        $total = $this->toFloat($get('total_repayment_amount'));

        if ($type === 'hire_purchase') {
            if ($changedField !== 'total_repayment_amount' && $installment > 0 && $termMonths > 0) {
                $total = round(($installment * $termMonths) + $deposit, 2);
                $set('total_repayment_amount', $total);
            }

            if ($total <= 0) {
                $total = $principal;
            }

            $financedRepayment = max($total - $deposit, 0);
            $set('details.financed_amount', round($financedRepayment, 2));

            if ($termMonths > 0 && $financedRepayment > 0) {
                $suggested = round($financedRepayment / $termMonths, 2);
                $set('details.suggested_installment', $suggested);
                $set('details.remaining_term_months', $termMonths);
                $set('monthly_installment', $suggested);
            }

            if ($total > 0) {
                $financedCashPrice = max($principal - $deposit, 0);
                if ($financedCashPrice > 0) {
                    $set('interest_rate', round(max((($financedRepayment - $financedCashPrice) / $financedCashPrice) * 100, 0), 2));
                }
            }

            return;
        }

        if ($type === 'personal_loan') {
            if ($changedField === 'interest_rate' && $interest >= 0) {
                $total = round($principal * (1 + ($interest / 100)), 2);
                $set('total_repayment_amount', $total);
            } elseif ($total > 0) {
                $derivedInterest = (($total - $principal) / $principal) * 100;
                $set('interest_rate', round(max(0, $derivedInterest), 2));
            }

            if ($termMonths > 0 && $total > 0) {
                $suggested = round($total / $termMonths, 2);
                $set('details.suggested_installment', $suggested);
                $set('details.remaining_term_months', $termMonths);

                if ($installment <= 0 || in_array($changedField, ['outstanding_balance', 'interest_rate', 'total_repayment_amount', 'details.term_months'], true)) {
                    $set('monthly_installment', $suggested);
                }
            }

            return;
        }

        if ($total > 0) {
            $derivedInterest = (($total - $principal) / $principal) * 100;
            $set('interest_rate', round(max(0, $derivedInterest), 2));

            return;
        }
    }

    private function toMonthlyAmount(float $amount, string $frequency): float
    {
        return match ($frequency) {
            'daily' => $amount * 30,
            'weekly' => $amount * 4.33,
            'bi_weekly' => $amount * 2.17,
            'monthly' => $amount,
            'quarterly' => $amount / 3,
            'annually' => $amount / 12,
            default => $amount,
        };
    }

    private function toFloat(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '', (string) $value);
    }

    private function isPurchaseDebtType(string $type): bool
    {
        return in_array($type, ['hire_purchase', 'vehicle_loan', 'mortgage'], true);
    }

    /**
     * @return array<string, string>
     */
    private function investmentSubtypeOptions(string $type): array
    {
        return match ($type) {
            'treasury_bills' => [
                '91_day' => '91-day Treasury Bill',
                '182_day' => '182-day Treasury Bill',
                '273_day' => '273-day Treasury Bill',
                '364_day' => '364-day Treasury Bill',
            ],
            'bonds' => [
                'government_bond' => 'Government Bond',
                'corporate_bond' => 'Corporate Bond',
                'infrastructure_bond' => 'Infrastructure Bond',
            ],
            'fixed_deposit' => [
                '30_day' => '30-day Fixed Deposit',
                '90_day' => '90-day Fixed Deposit',
                '180_day' => '180-day Fixed Deposit',
                '365_day' => '365-day Fixed Deposit',
            ],
            'stocks' => [
                'luse_equity' => 'LuSE Listed Equity',
                'regional_equity' => 'Regional Equity',
                'global_equity' => 'Global Equity',
            ],
            'unit_trust', 'mutual_fund' => [
                'money_market' => 'Money Market Fund',
                'balanced_fund' => 'Balanced Fund',
                'equity_fund' => 'Equity Fund',
                'income_fund' => 'Income Fund',
            ],
            'real_estate' => [
                'rental_property' => 'Rental Property',
                'land_bank' => 'Land Bank',
                'commercial_property' => 'Commercial Property',
            ],
            'farming' => [
                'crop_farming' => 'Crop Farming',
                'livestock' => 'Livestock',
                'mixed_farming' => 'Mixed Farming',
            ],
            default => [],
        };
    }
}
