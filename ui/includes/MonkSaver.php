<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/19/11
 * Time: 4:21 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class MonkSaver {

    function __construct()
    {
        if((!isset($_POST['page'])) && (!isset($_POST['labels'])))
            throw new Exception('There is no data to save');
    }



    /**
     * Stores the page remotely at the Monk server
     * Collects it's data from the $_POST variable
     * @return void
     */
    public function savePage()
    {
        $user = MonkUser::getInstance();
        //$this->savePageToMonk();
//        switch($user->getRole())
//        {
//            case UserRole::TRAINEE:
//                $this->savePageToDisk();
//                break;
//            case UserRole::VERIFIER:
//            case UserRole::TRANSCRIBER:
//                $this->savePageToMonk();
//                break;
//            default:
//                throw new Exception("You are not allowed to do anything");
//                break;
//        }
        $this->savePageToMonk();
    }

    /*
     * Saves the page to Monk.
     */
    private function savePageToMonk()
    {
        $user       = MonkUser::getInstance();
        $results    = array();
        $page  = $_SESSION[Config::PAGE_STORE];

        $body = <<<BODY
<finish_transcribe_page>
  <authtoken>{$_SESSION['token']}</authtoken>
  <labels>

BODY;

        foreach($_POST['labels'] as $label)
        {
            // TODO: validate post data?

            $shear = Config::DEFAULT_SHEAR;

            if($page instanceof Page)
                $shear = $page->getShear();
            else
                throw new Exception('Error: Variable stored is not of type Page. Please consult developer');

            $wordLabel = new NewWordLabel($label, $shear);
            $uri = http_build_query($wordLabel->getURI());

            // looks a bit strange, but the method has been rebuild from CGI to REST, keeping the old code
            $labelData = $wordLabel->getURI();
            // OLD version, CGI call
//            $uri = '?cmd=newwordlabel-api'                   .
//                   '&token='  . $user->getToken()            .
//                   '&appid='  . Config::APPID                .
//                   '&label='  . urlencode($label['txt'])     .
//                   '&dist='   . $label['dist']               .
//                   '&x='      . $label['x']                  .
//                   '&y='      . $label['y']                  .
//                   '&w='      . $label['w']                  .
//                   '&h='      . $label['h']                  .
//                   '&lineid=' . urlencode($label['lineid']);




            $url = Config::STORE_URL . '?' . $uri;        /* complete url with all options to store word */

            // It might look a little off, but it works
            // maybe a better way would be to create an array and convert it to XML
            $lineId = $labelData["lineid"];
            $body .= <<<LABELS
<label>
    <line_no>$lineId</line_no>
        <roi>

LABELS;
            foreach($labelData['coordinates'] as $coordinate)
            {
                $body .= <<<ROI
            <pos>

ROI;
                foreach($coordinate as $axis=>$value)
                {
                    $body .= <<< COORDINATES
                <{$axis}>{$value}</{$axis}>

COORDINATES;

                }
                $body .= <<<ROI
            </pos>

ROI;
            }
            $body .= <<<LABELS
        </roi>
    <word>{$labelData['label']}</word>
</label>

LABELS;
        }

        $body .= <<<BODY

  </labels>
</finish_transcribe_page>
BODY;

        $returnResult = '';

        $pestXML = new PestXML(Config::REST_SERVER);
        $url = "/rest/finish_transcribe_page/" . urlencode($page->institution) . "/" . urlencode($page->collection) . "/" . urlencode($page->book) . "/" . urlencode($page->pageNumber);

        //DEBUG
//        $log = new KLogger('body.log', KLogger::DEBUG);
//        $log->LogDebug($body);


        try
        {
            $returnResult = $pestXML->post($url, $body);
        }
        catch (Exception $e)
        {
            // TODO catch exceptions
            echo $e->getMessage();
            die();
        }
        //echo $this->parseResult($results);
        echo $returnResult->status;
    }

    private function savePageToDisk()
    {
        $user    = MonkUser::getInstance();
        $data    = array();

        // store labels local, just store them as json
        foreach($_POST['labels'] as $label)
        {
            $data[] = array(
                $label['lineid'],
                $label['x'],
                $label['y'],
                $label['w'],
                $label['h'],
                $label['txt']
            );
        }


        if(isset($_POST['page']) && $fp = fopen(ROOT . Config::SAVE_DIR . DIRECTORY_SEPARATOR . $user->getUsername() . '.' . $_POST['page'] .'.csv',"w"))
        {
            foreach($data as $field)
                fputcsv($fp, $field);
            
            fclose($fp);
            echo "Succesvol weggeschreven";
        }
        else
        {
            echo "Error: Kan geen bestand wegschrijven. Directory " . ROOT . Config::SAVE_DIR . " wel schrijfbaar?";
        }

    }

    /*
     * Used to parse the result returned from the CGI call to save the labels. Obsolete with REST calls
     */
    private function parseResult($results)
    {
        $error = false;

        foreach($results as $result) {
            if(stripos($result, '...successfully written to disk') === false)
            {
                $error = true;
                break;
            }
        }
        if($error)
            return "Fout met opslaan";
        else
            return "Succesvol opgeslagen";
    }


}