<?php
//include_once '../simpl/simple_html_dom.php';
include_once '../Common.class.php';
class MainVacationPageParser_stackoverflow
{
    function linksParse($url, $tag)
    {
        $common = new Common();
        $curlResult = $common->curlInit($url);
        $html = new simple_html_dom();
        $html->load($curlResult);
        $fullLinksToJobs = array('linksToJob' => array(), 'endOfCycle' => 'true');
        foreach ($html->find('div[class=listResults -jobs list jobs]') as $element) {
            foreach ($element->find('div.listResults div.tags') as $tagsName) {
                if (strpos(strtolower($tagsName), strtolower($tag)) !== false) {
                    $partLinksToJob[] = $tagsName->parentNode()->childNodes(2)->childNodes(0)->href;
                } else {
                    $fullLinksToJobs['endOfCycle'] = false;
                    break 2;
                }
            }
        }
        if ($partLinksToJob != null && is_array($partLinksToJob)) {
            foreach ($partLinksToJob as $linksPart) {
                $fullLinksToJobs['linksToJob'][] = 'http://careers.stackoverflow.com/' . $linksPart;
            }
        }
        return $fullLinksToJobs;
    }

    public function allLinks($searchTag)
    {
        $url = 'http://careers.stackoverflow.com/jobs?searchTerm=' . $searchTag;
        $html = file_get_html($url);
        foreach ($html->find('#index-hed h2 span') as $element) {
            preg_match("/\d+/", $element->innertext, $countOfVacancy);
        }
        $countOfVacancy = $countOfVacancy[0];
        $countOfPages = ceil($countOfVacancy / 25);
        for ($i = 1; $i <= $countOfPages; $i++) {
            if ($i == 1) {
                $urlWithPageNumber = $url;
            } else {
                $urlWithPageNumber = $url . "&pg=$i";
            }
            $linksToJob = $this->linksParse($urlWithPageNumber, $searchTag);
            if ($linksToJob != null && is_array($linksToJob))
                $allLinksToJob = array_merge((array)$allLinksToJob, $linksToJob['linksToJob']);
            if ($linksToJob['endOfCycle'] === false)
                break;
        }

        return $allLinksToJob;

    }
}
