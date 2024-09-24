<?php
require_once 'DBConnection.php';
require 'vendor/autoload.php';
use Symfony\Component\Panther\Client;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DevCoder\DotEnv;

$absolutePathToEnvFile = __DIR__ . '/.env';

(new DotEnv($absolutePathToEnvFile))->load();

class AlkoScraper
{
    private $db;

    private $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0.2 Safari/605.1.15',
        'Mozilla/5.0 (Linux; Android 10; SM-G950F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Mobile Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Gecko/20100101 Firefox/89.0',
    ];

    public function __construct()
    {
        $this->db = DBConnection::getInstance();
    }

    public function scrape()
    {
        $userAgent = $this->userAgents[array_rand($this->userAgents)];

        $client = Client::createChromeClient(null, ['--headless'], ['user-agent' => $userAgent], 'https://www.alko.fi/');
        $crawler = $client->request('GET', 'https://www.alko.fi/valikoimat-ja-hinnasto/hinnasto');


        $client->waitFor('.teaser-heading');

        $excelUrl = '';
        try {
            $links = $crawler->filter('a');
            foreach ($links as $link) {
                $linkText = $link->getText();
                if (strpos($linkText, 'Alkon hinnasto Excel-tiedostona') !== false) {
                    $excelUrl = $link->getAttribute('href');
                    echo "Found Excel file URL: " . $excelUrl . "\n";
                    break;
                }
            }
            if (empty($excelUrl)) {
                throw new Exception("No Excel links found.");
            }
        } catch (Exception $e) {
            echo "An error occurred while searching for links: " . $e->getMessage() . "\n";
            return;
        }

        $excelFile = 'alko_price_list.xlsx';
        file_put_contents($excelFile, file_get_contents('https://www.alko.fi'.$excelUrl));

        // Loading the downloaded excel file
        $spreadsheet = IOFactory::load($excelFile);
        $sheet = $spreadsheet->getActiveSheet();

        $apiKey = getenv('CURRENCY_LAYER_API_KEY');
        $currencyApiUrl = "http://api.currencylayer.com/live?access_key=$apiKey&currencies=GBP&source=EUR&format=1";
        $currencyData = json_decode(file_get_contents($currencyApiUrl), true);
        var_dump($currencyData);

        if (isset($currencyData['quotes']['EURGBP'])) {
            $eurToGbp = $currencyData['quotes']['EURGBP'];
        } else {
            echo "API key is expired.\n";
            return; 
        }

        // Fetching data from Excel and save it to the database
        foreach ($sheet->getRowIterator(5) as $row) { 
            $number = $sheet->getCell('A' . $row->getRowIndex())->getValue();
            $name = $sheet->getCell('B' . $row->getRowIndex())->getValue();
            $bottlesize = $sheet->getCell('D' . $row->getRowIndex())->getValue();
            $price = (float) str_replace(',', '', $sheet->getCell('E' . $row->getRowIndex())->getValue()); 
            $priceGBP = number_format($price * $eurToGbp, 2, '.', ''); 

            
            $query = $this->db->prepare("
                INSERT INTO products (number, name, bottlesize, price, priceGBP, timestamp)
                VALUES (:number, :name, :bottlesize, :price, :priceGBP, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE 
                    name = VALUES(name), 
                    bottlesize = VALUES(bottlesize), 
                    price = VALUES(price), 
                    priceGBP = VALUES(priceGBP), 
                    timestamp = CURRENT_TIMESTAMP
            ");

            
            $query->execute([
                ':number' => $number,
                ':name' => $name,
                ':bottlesize' => $bottlesize,
                ':price' => $price,
                ':priceGBP' => (float) str_replace(',', '', $priceGBP) 
            ]);
        }


        echo "Data fetched and updated in the database!\n";
    }
}

$scraper = new AlkoScraper();
$scraper->scrape();
