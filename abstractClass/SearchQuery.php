<?php

abstract class SearchQuery
{
    abstract protected function search($searchTagAndCity, $searchObject);

    public function getSearch($searchTagAndCity, $searchObject)
    {
        return $this->search($searchTagAndCity, $searchObject);
    }

    protected function findKeyWords($fullMapArray, $searchObject)
    {
        $searchResultMap = array();
        foreach ($fullMapArray as $idAndCompanyAndText) {
            foreach ($searchObject as $searchStringObject) {
                if ($searchStringObject->search !== null) {
                    $isAllKeysPresented = $this->isKeyPresent($searchStringObject->search, $idAndCompanyAndText['text']);
                }
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

    protected function isKeyPresent($keyArrays, $idAndCompanyAndText)
    {
        foreach ($keyArrays[0] as $key => $data) {
            $lowSearchString = $keyArrays[0]->$key;
            if (preg_match("/$lowSearchString\b/i", $idAndCompanyAndText)) {
                return true;
            }
        }
        return false;
    }

    protected function insertKeyWord($searchResultMap, $searchString)
    {
        if (null != $this->checkKey($searchResultMap, $searchString)) {
            $searchResultMap[$searchString]++;
        } else {
            $searchResultMap[$searchString] = 1;
        }
        return $searchResultMap;
    }

    protected function putZeroIfKeyNotPresent($searchResultMap, $searchObject)
    {
        foreach ($searchObject as $key => $searchStringObject) {
            if (null == $this->checkKey($searchResultMap, $searchStringObject->name)) {
                $searchResultMap[$searchStringObject->name] = 0;
            }
        }
        return $searchResultMap;
    }

    function checkKey($array, $key)
    {
        return array_key_exists($key, $array) ? $array[$key] : null;
    }

}