<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Created by Ahmed Maher Halima.
 * Email: phpcodertop@gmail.com
 * github: https://github.com/phpcodertop
 * Date: 6/26/2022
 * Time: 6:12 AM
 */

class Yelp {

    private $baseUrl = 'https://www.yelp.com/search/snippet';
    private $yelpUrl = 'https://www.yelp.com';
    private $searchWord;
    private $location;
    private $start = 0;
    private $maxResults;
    private $data = array();
    private $sheetData = array();

    private $searchQuery = array(
        'find_desc' => '',
        'find_loc' => '',
        'request_origin' => 'user',
    );

    /**
     * @param string $searchWord
     * @param string $location
     */
    public function __construct(string $searchWord,
    string $location, int $maxResults = 10)
    {
        $this->searchWord = $searchWord;
        $this->location = $location;
        $this->maxResults = $maxResults;
    }

    public  function scrape(): array
    {
        $steps = ceil($this->maxResults / 10);

        for ($i = 0; $i < $steps; $i++)
        {
            $this->start += 10;
            $this->getData();
        }
        return $this->data;
    }


    private function getData(): void
    {
        $this->searchQuery['start'] = $this->start;
        $this->searchQuery['find_desc'] = $this->searchWord;
        $this->searchQuery['find_loc'] = $this->location;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->baseUrl
                .'?'. http_build_query($this->searchQuery),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'accept: */*',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        try
        {
            $response = json_decode($response, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            echo $e->getMessage();
        }

        $results = $response->searchPageProps->mainContentComponentsListProps;

        $filteredData =  array_filter($results, static function ($result) {
            return $result->searchResultLayoutType !== 'separator';
        });
        $this->data = array_merge($this->data, $filteredData);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function export()
    {
        $this->format();
        if (count($this->sheetData) === 1) {
            echo 'No data';
            return;
        }

        $fileName =  'yelp_export_'.date('m_d_Y_hi').'.xls';
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->fromArray($this->sheetData);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $fileName .'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    private function format(): void
    {
        $this->sheetData[] = [
            'Name',
            'Phone',
            'formattedAddress',
            'Photo',
            'Rating',
            'reviewCount',
            'businessUrl',
        ];

        foreach ($this->data as $listing) {
            $this->sheetData[] = $this->formattedListing($listing);
        }
    }

    private function formattedListing($listing): array
    {
        return [
            $listing->searchResultBusiness->name,
            $listing->searchResultBusiness->phone,
            $listing->searchResultBusiness->formattedAddress,
            $listing->scrollablePhotos->photoList[0]->src, // photo
            $listing->searchResultBusiness->rating,
            $listing->searchResultBusiness->reviewCount,
            $this->yelpUrl . $listing->searchResultBusiness->businessUrl,
        ];
    }

}
