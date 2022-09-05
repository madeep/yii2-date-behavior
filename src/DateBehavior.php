<?php

namespace deepha\date\behavior;

use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Class DateBehavior
 * @package deepha\date\behavior
 */
class DateBehavior extends Behavior
{
    public $owner;

    /**
     * Data Inizio
     * @var string
     */
    public $dateStart = 'date_start';

    /**
     * Data Fine
     * @var string
     */
    public $dateEnd = 'date_end';

    /**
     * Data Singola
     * @var string
     */
    public $singleDate = 'date';

    /**
     * @var array
     */
    public $groupOfDates = [];

    /**
     * Se parsare una data singola
     * @var bool
     */
    public $parseSingleDate = false;

    /**
     * Se parsare date start && date end
     * @var bool
     */
    public $parseDateRange = false;

    /**
     * Parsa gruppo di date
     * @var bool
     */
    public $parseGroupOfDates = false;

    /**
     * @var bool
     */
    public $isDateTime = false;

    /**
     * Formato MYSQL Anno / Mese / Giorno
     */
    CONST MYSQL_DATE_FORMAT = "Y-m-d";

    /**
     * Formato MYSQL Anno / Mese / Giorno - Ora / Min / Sec
     */
    CONST MYSQL_DATETIME_FORMAT = "Y-m-d H:i:s";

    /**
     * Formato EUROPEO Data
     */
    CONST EUROPEAN_DATE_FORMAT = "d/m/Y";

    /**
     * Formato EUROPEO Data, ora, minuto, secondo
     */
    CONST EUROPEAN_DATETIME_FORMAT = "d/m/Y H:i:s";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if($this->parseDateRange){
            if (!isset($this->dateStart) || !isset($this->dateEnd)) {
                throw new InvalidConfigException('The "dateStart" and "dateEnd" properties must be specified.');
            }
        }
        if (isset($this->parseSingleDate)) {
            if (!isset($this->singleDate)) {
                throw new InvalidConfigException('The "single date" must be specified.');
            }
        }
        if (isset($this->parseGroupOfDates)) {
            if(!isset($this->groupOfDates) && empty($this->groupOfDates)){
                throw new InvalidConfigException('The "group of dates" must be specified.');
            }
        }
    }

    /**
     * Gestori di evento autorizzati al Behavior
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'processDateBeforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'processDateBeforeSave',
            ActiveRecord::EVENT_AFTER_FIND => 'processDateAfterFind',
        ];
    }

    /**
     * Converte data in formato DB MYSQL
     * Trigger Pre Inserimento e Pre Modifica record
     * Verifica se effettuare conversione per singola data,per coppia data inizio e data fine o gruppo date
     * @param $event
     *
     */
    public function processDateBeforeSave($event)
    {
        if($this->parseDateRange){
            $this->parseDateRangeBeforeSave();
        }
        if($this->parseSingleDate){
            $this->parseSingleDateBeforeSave();
        }
        if($this->parseGroupOfDates){
            $this->parseGroupOfDatesBeforeSave();
        }

    }

    /**
     * Converte data nel formato d/m/Y h:i:s
     * Trigger Post Inserimento e Post Modifica record
     * Verifica se effettuare conversione per singola data,per coppia data inizio e data fine o gruppo date
     * @param $event
     *
     */
    public function processDateAfterSave($event)
    {
        if($this->parseDateRange){
            $this->parseDateRangeAfterFind();
        }
        if($this->parseSingleDate){
            $this->parseSingleDateAfterFind();
        }
        if($this->parseGroupOfDates){
            $this->parseGroupOfDatesAfterFind();
        }
    }

    /**
     * FROM ISO 8601 big-endian to little-endian
     * Trigger Post Ricerca
     * @param $event
     */
    public function processDateAfterFind($event)
    {
        if($this->parseDateRange) {
            $this->parseDateRangeAfterFind();
        }
        if($this->parseSingleDate) {
            $this->parseSingleDateAfterFind();
        }
        if($this->parseGroupOfDates){
            $this->parseGroupOfDatesAfterFind();
        }
    }

    /**
     * Single date BEFORE SAVE
     */
    private function parseSingleDateBeforeSave(){
        if(!$this->isDateTime) {
            $singleDate = \DateTime::createFromFormat(self::EUROPEAN_DATE_FORMAT, $this->owner->{$this->singleDate});
            $this->owner->{$this->singleDate} = $singleDate->format(self::MYSQL_DATE_FORMAT);
        }else{
            $singleDate = \DateTime::createFromFormat(self::EUROPEAN_DATETIME_FORMAT, $this->owner->{$this->singleDate});
            $this->owner->{$this->singleDate} = $singleDate->format(self::MYSQL_DATETIME_FORMAT);
        }
    }

    /**
     * Single date AFTER FIND
     */
    private function parseSingleDateAfterFind(){

        if(!$this->isDateTime) {
            $singleDate = \DateTime::createFromFormat(self::MYSQL_DATE_FORMAT, $this->owner->{$this->singleDate});
            $this->owner->{$this->singleDate} = $singleDate->format(self::EUROPEAN_DATETIME_FORMAT);
        }else{
            $singleDate = \DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $this->owner->{$this->singleDate});
            $this->owner->{$this->singleDate} = $singleDate->format(self::EUROPEAN_DATETIME_FORMAT);
        }

    }

    /**
     * Date range BEFORE SAVE
     */
    private function parseDateRangeBeforeSave(){
        if(!$this->isDateTime){
            $dateStart = \DateTime::createFromFormat(self::EUROPEAN_DATE_FORMAT, $this->owner->{$this->dateStart});
            $dateEnd = \DateTime::createFromFormat(self::EUROPEAN_DATE_FORMAT, $this->owner->{$this->dateEnd});
            $this->owner->{$this->dateStart} = $dateStart->format(self::MYSQL_DATE_FORMAT) ;
            $this->owner->{$this->dateEnd} = $dateEnd->format(self::MYSQL_DATE_FORMAT) ;
        }else{
            $dateStart = \DateTime::createFromFormat(self::EUROPEAN_DATETIME_FORMAT, $this->owner->{$this->dateStart});
            $dateEnd = \DateTime::createFromFormat(self::EUROPEAN_DATETIME_FORMAT, $this->owner->{$this->dateEnd});
            $this->owner->{$this->dateStart} = $dateStart->format(self::MYSQL_DATETIME_FORMAT) ;
            $this->owner->{$this->dateEnd} = $dateEnd->format(self::MYSQL_DATETIME_FORMAT) ;
        }
    }

    /**
     * Date range AFTER FIND
     */
    private function parseDateRangeAfterFind(){
        if(!$this->isDateTime){
            $dateStart = \DateTime::createFromFormat(self::MYSQL_DATE_FORMAT, $this->owner->{$this->dateStart});
            $dateEnd = \DateTime::createFromFormat(self::MYSQL_DATE_FORMAT, $this->owner->{$this->dateEnd});
            if($dateStart == false){
                return;
            }else {
                $this->owner->{$this->dateStart} = $dateStart->format(self::EUROPEAN_DATE_FORMAT);
            }
            if($dateEnd == false){
                return;
            }else {
                $this->owner->{$this->dateEnd} = $dateEnd->format(self::EUROPEAN_DATE_FORMAT);
            }
        }else{
            $dateStart = \DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $this->owner->{$this->dateStart});
            $dateEnd = \DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $this->owner->{$this->dateEnd});
            if($dateStart == false){
                return;
            }else {
                $this->owner->{$this->dateStart} = $dateStart->format(self::EUROPEAN_DATETIME_FORMAT);
            }
            if($dateEnd == false){
                return;
            }else {
                $this->owner->{$this->dateEnd} = $dateEnd->format(self::EUROPEAN_DATETIME_FORMAT);
            }
        }
    }

    /**
     * Group of dates BEFORE SAVE
     */
    private function parseGroupOfDatesBeforeSave(){
        foreach($this->groupOfDates as $k => $v){
            if ($v['datetime'] != true) {
                if(isset($this->owner->{$k}) && $this->owner->{$k} != null) {
                    $singleDate = \DateTime::createFromFormat(self::EUROPEAN_DATE_FORMAT, $this->owner->{$k});

                    if($singleDate == false){
                        return;
                    }else{
                        $this->owner->{$k} = $singleDate->format(self::MYSQL_DATE_FORMAT);
                    }
                }
            } else {
                if(isset($this->owner->{$k}) && $this->owner->{$k} != null) {
                    if ($v['inverse'] != true) {
                        $singleDate = \DateTime::createFromFormat(self::EUROPEAN_DATETIME_FORMAT, $this->owner->{$k});
                    }else{
                        $singleDate = \DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $this->owner->{$k});
                    }
                    if($singleDate == false){
                        return;
                    }else {
                        $this->owner->{$k} = $singleDate->format(self::MYSQL_DATETIME_FORMAT);
                    }
                }
            }
        }
    }

    /**
     * Group of dates AFTER FIND
     */
    private function parseGroupOfDatesAfterFind(){
        foreach($this->groupOfDates as $k => $v){
            if($v['datetime'] != true) {
                if(isset($this->owner->{$k}) && $this->owner->{$k} != null){
                    $singleDate = \DateTime::createFromFormat(self::MYSQL_DATE_FORMAT, $this->owner->{$k});
                    if($singleDate == false){
                        return;
                    }else {
                        $this->owner->{$k} = $singleDate->format(self::EUROPEAN_DATE_FORMAT);
                    }
                }
            }else{
                if(isset($this->owner->{$k}) && $this->owner->{$k} != null){
                    $singleDate = \DateTime::createFromFormat(self::MYSQL_DATETIME_FORMAT, $this->owner->{$k});
                    if($singleDate == false){
                        return;
                    }else {
                        $this->owner->{$k} = $singleDate->format(self::EUROPEAN_DATETIME_FORMAT);
                    }
                }
            }
        }
    }

    private function createFromFormat($dateFormat){

    }
}