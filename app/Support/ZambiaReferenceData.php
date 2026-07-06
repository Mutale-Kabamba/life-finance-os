<?php

declare(strict_types=1);

namespace App\Support;

class ZambiaReferenceData
{
    /**
     * @return array<string, string>
     */
    public static function provinceOptions(): array
    {
        return [
            'Central' => 'Central',
            'Copperbelt' => 'Copperbelt',
            'Eastern' => 'Eastern',
            'Luapula' => 'Luapula',
            'Lusaka' => 'Lusaka',
            'Muchinga' => 'Muchinga',
            'Northern' => 'Northern',
            'North-Western' => 'North-Western',
            'Southern' => 'Southern',
            'Western' => 'Western',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function districtsByProvince(): array
    {
        return [
            'Central' => ['Kabwe', 'Kapiri Mposhi', 'Chibombo', 'Serenje', 'Mkushi', 'Mumbwa', 'Itezhi-Tezhi', 'Chitambo', 'Ngabwe', 'Shibuyunji', 'Luano'],
            'Copperbelt' => ['Ndola', 'Kitwe', 'Mufulira', 'Chingola', 'Luanshya', 'Kalulushi', 'Chililabombwe', 'Lufwanyama', 'Masaiti', 'Mpongwe'],
            'Eastern' => ['Chipata', 'Katete', 'Petauke', 'Lundazi', 'Chadiza', 'Sinda', 'Vubwi', 'Mambwe', 'Nyimba', 'Chama', 'Lumezi', 'Kasenengwa', 'Chasefu'],
            'Luapula' => ['Mansa', 'Kawambwa', 'Samfya', 'Nchelenge', 'Mwense', 'Chiengi', 'Milenge', 'Chembe', 'Chifunabuli', 'Lunga'],
            'Lusaka' => ['Lusaka', 'Chongwe', 'Kafue', 'Luangwa', 'Rufunsa', 'Chilanga', 'Shamakumba'],
            'Muchinga' => ['Chinsali', 'Nakonde', 'Isoka', 'Mpika', 'Shiwangandu', 'Kanchibiya', 'Lavushimanda'],
            'Northern' => ['Kasama', 'Mbala', 'Mungwi', 'Mpulungu', 'Luwingu', 'Mporokoso', 'Chilubi', 'Lunte', 'Senga Hill', 'Lupososhi', 'Nsama'],
            'North-Western' => ['Solwezi', 'Kasempa', 'Mwinilunga', 'Zambezi', 'Kabompo', 'Mushindamo', 'Kalumbila', 'Manyinga', 'Mufumbwe', 'Ikelenge'],
            'Southern' => ['Choma', 'Livingstone', 'Monze', 'Mazabuka', 'Kalomo', 'Namwala', 'Pemba', 'Gwembe', 'Sinazongwe', 'Siavonga', 'Zimba', 'Kazungula'],
            'Western' => ['Mongu', 'Senanga', 'Kalabo', 'Sesheke', 'Lukulu', 'Kaoma', 'Nkeyema', 'Shangombo', 'Nalolo', 'Limulunga', 'Sikongo', 'Sioma', 'Mitete', 'Mulobezi'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function districtOptions(?string $province): array
    {
        if (! $province || ! isset(self::districtsByProvince()[$province])) {
            return [];
        }

        $districts = self::districtsByProvince()[$province];

        return array_combine($districts, $districts) ?: [];
    }

    /**
     * @return array<string, string>
     */
    public static function businessIndustryOptions(): array
    {
        $industries = [
            'Agriculture and Agribusiness',
            'Aquaculture and Fisheries',
            'Mining and Quarrying',
            'Manufacturing - Food Processing',
            'Manufacturing - Textiles and Apparel',
            'Manufacturing - Metal and Fabrication',
            'Construction and Civil Works',
            'Retail and Wholesale Trade',
            'E-commerce and Online Retail',
            'Hospitality and Tourism',
            'Transport and Logistics',
            'Real Estate and Property Management',
            'Financial Services and Insurance',
            'Microfinance and SACCO',
            'Education and Training',
            'Healthcare and Pharmaceuticals',
            'ICT and Software Services',
            'Telecommunications',
            'Energy and Utilities',
            'Professional Services',
            'Legal Services',
            'Accounting and Audit',
            'Marketing and Media',
            'Creative and Design Services',
            'Beauty and Personal Care',
            'Cleaning and Facility Services',
            'Security Services',
            'Motor Trade and Auto Services',
            'Non-profit and Community Organisation',
            'Public Sector Contractor',
            'Import and Export',
            'General Merchandise',
            'Other',
        ];

        return array_combine($industries, $industries) ?: [];
    }
}
