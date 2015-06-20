<?php


include_once '../simpl/simple_html_dom.php';
include_once 'ProcessingDataArrayWithText_dou.class.php';
include_once 'MainVacationPageParser_dou.class.php';
include_once 'CacheGetter_dou.class.php';
include_once 'ParserIdAndCompanyFromLinks_dou.class.php';

class SearchQuery_dou
{

    function search($searchTagAndCity, $searchObject)
    {

        $mainVacationPageParser = new MainVacationPageParser_dou();
        $linksToJobsArray = $mainVacationPageParser->parseNextPart($searchTagAndCity);

        $parserIdAndCompanyFromLinks = new ParserIdAndCompanyFromLinks_dou();
        $idAndCompanyArray = $parserIdAndCompanyFromLinks->processingReferences($linksToJobsArray);

        $cacheGetter = new CacheGetter_dou();
        $idAndCompaniesAndMayNotBeCompleteTextArray = $cacheGetter->formationMapWithText($idAndCompanyArray);

        $processingDataArrayWithText = new ProcessingDataArrayWithText_dou();
        $fullMapArray = $processingDataArrayWithText->takeTheMissingText($idAndCompaniesAndMayNotBeCompleteTextArray);


        $searchResultMap = $this->findKeyWords($fullMapArray, $searchObject);
        return $searchResultMap;
    }

    function findKeyWords($fullMapArray, $searchObject)
    {
        foreach ($fullMapArray as $idAndCompanyAndText) {
            foreach ($searchObject as $searchStringObject) {
                $isAllKeysPresented = $this->isKeyPresent($searchStringObject->search, $idAndCompanyAndText['text']);
                if ($searchStringObject->notPresented !== null) {
                    $isPresentedKeyPresent = $this->isKeyPresent(
                        $searchStringObject->notPresented,
                        $idAndCompanyAndText['text']);
                }
                if ($isAllKeysPresented && !$isPresentedKeyPresent) {
                    $searchResultMap = $this->insertKeyWord($searchResultMap, $searchStringObject->name);
                }
            }
        }
        return $this->putZeroIfKeyNotPresent($searchResultMap, $searchObject);
    }

    function isKeyPresent($keyArrays, $idAndCompanyAndText)
    {
        $idAndCompanyAndText = str_replace('href="javascript:;', '', $idAndCompanyAndText);
        $idAndCompanyAndText = str_replace('type="text/javascript', '', $idAndCompanyAndText);
        foreach ($keyArrays[0] as $key => $data) {
            $lowSearchString = $keyArrays[0]->$key;
            if (preg_match("/\b($lowSearchString)\b/i", $idAndCompanyAndText)) {
                return true;
            }
        }
        return false;
    }

    function insertKeyWord($searchResultMap, $searchString)
    {
        if (null != $searchResultMap[$searchString]) {
            $searchResultMap[$searchString]++;
        } else {
            $searchResultMap[$searchString] = 1;
        }
        return $searchResultMap;
    }

    function putZeroIfKeyNotPresent($searchResultMap, $searchObject)
    {
        foreach ($searchObject as $key => $searchStringObject) {
            if (null == $searchResultMap[$searchStringObject->name]) {
                $searchResultMap[$searchStringObject->name] = 0;
            }
        }
        return $searchResultMap;

    }

}

