<?php
/**
 * Created by De Ontwikkelfabriek.
 * User: Postie
 * Date: 7/7/11
 * Time: 3:02 PM
 * Copyright 2011 De Ontwikkelfabriek
 */
 
class Page {

    /* navis id of he page */
    public $id;

    public $shear;

    public $pageNumber;

    public $institution;

    public $collection;

    public $book;




    /* collection of lines that make up the page */
    private $lines = array();

    function __construct($id, $shear = Config::DEFAULT_SHEAR)
    {
        $this->id = $id;
        $this->shear = $shear;
    }

    /**
     * Add a line to the page
     * @param Line $line
     * @return void
     */
    function add(Line $line)
    {
        $this->lines[] = $line;
    }

    /**
     * Add multiple lines to the page
     * @param  $lines
     * @return void
     */
    function addLines($lines)
    {
        foreach($lines as $line)
            if($line instanceof Line)
                $this->add($line);
    }

    /**
     * Returns the lineid's of the page as a JSON string
     * @return string
     */
    public function lineIDsAsJson()
    {
        $return = array();
        foreach($this->lines as $line)
            $return[] = $line->getId();
        return json_encode($return);
    }

    /**
     * Returns the Line object or null if it's not in the page
     * @param  $id
     * @return Line object
     */
    public function getLine($id)
    {
        foreach($this->lines as $line)
            if($line->getId() == $id)
                return $line;
        return null;
    }

    public function getImageForLine($id)
    {
        foreach($this->lines as $line)
            if($line->getId() == $id)
                return $line->image;
        return null;
    }


    /**
     * Returns the labels of a line as a JSON string
     * @param  integer lineid
     * @return string JSON
     */
    public function lineLabelsAsJSON($lineId)
    {
        /* session only contains current page */
        foreach($_SESSION[Config::PAGE_STORE]->lines as $line)
        {
            if($line->getId() == $lineId)
            {
                return $line->asJSON();
            }
        }
    }

    /**
     * Sets the shear of the page
     * @param $shear float
     * @return void
     */
    public function setShear($shear)
    {
        $this->shear = $shear;
    }

    /**
     * Returns the shear of the current page as an integer
     * @return int shear
     */
    public function getShear()
    {
        return $this->shear;
    }

    public function shearAsJSON()
    {
        return json_encode(array("shear", $this->shear));
    }

}