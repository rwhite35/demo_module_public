<?php
namespace Acme\Utilities;
date_default_timezone_set('EST');

/**
 * Acme\Utilities | vendor/acme/utilities/src/CalculateBankHolidays
 *
 * this class assembles Bank holidays for the current year.
 * requires PHP extendsion Calendar (Calendar enabled)
 * takes know (or fixed) dates and applies the current year.
 * returns a multi-dim array of dates.
 *
 * @package Acme\Utilities
 * @subpackage CalculateBankHolidays
 * @version 1.0
 * @since 2021-06-17
 *
 * calendar reference
 * @link https://www.calendar-365.com/holidays
 * @link https://www.frbservices.org/about/holiday-schedules/index.html
 */

class CalculateBankHolidays
{
		var $bankHolidays = [];
		var $year;

		public function __construct()
		{
			$this->year = date("Y");
			$this->calculateDates($this->year);
		}

		/**
 		 * calculateDates
 		 * determines the holiday for given year($yr) by its starting date relationship
 		 * to the switch case weekday. Switch case weeekdays enumerate from Sun-Sat.
 		 * Sunday(0), Monday(1), Tuesday(2), Wednesday(3), Thursday(4), Friday(5), Saturday(6)
	     *
		 * @return Array bankHolidays, indexed array of all holiday dates for a given year
 		 */
		public function calculateDates($yr)
		{
            // New Years Day
            // when falls on Sat - observe on Fri, when on Sun - observed Mon 
    	   switch ( date("w", strtotime("$yr-01-01 12:00:00")) ) 
    	   {
    	       case 0: $bankHolidays[] = "$yr-01-02";
    	           break;
    	       case 6: $bankHolidays[] = "$yr-12-31";
    	           break;
    	       default: $bankHolidays[] = "$yr-01-01";
    	   }


            // MLK Day - always on a Monday
            switch ( date("w", strtotime("$yr-01-15 12:00:00")) ) 
            {
                case 0: $bankHolidays[] = "$yr-01-16";
                    break;
                case 1: $bankHolidays[] = "$yr-01-15";
                    break;
                case 2: $bankHolidays[] = "$yr-01-15";
                    break;
                case 3: $bankHolidays[] = "$yr-01-20";
                    break;
                case 4: $bankHolidays[] = "$yr-01-19";
                    break;
                case 5: $bankHolidays[] = "$yr-01-18";
                    break;
                case 6: $bankHolidays[] = "$yr-01-17";
                    break;
            }

            
			// Presidents Day - On Mondays usually the third in Feb.
			// Note: 15th does not land on a Friday in the next six years
			switch ( date("w", strtotime("$yr-02-15 12:00:00")) ) 
			{
				case 0: $bankHolidays[] = "$yr-02-16";
					break;
				case 1: $bankHolidays[] = "$yr-02-15";
					break;
				case 2: $bankHolidays[] = "$yr-02-21";
					break;
				case 3: $bankHolidays[] = "$yr-02-20";
					break;
				case 4: $bankHolidays[] = "$yr-02-19";
					break;
				case 5: $bankHolidays[] = "$yr-02-15";
				    break;
				case 6: $bankHolidays[] = "$yr-02-17";
					break;
			}


			// Good Friday
			$bankHolidays[] = date("Y-m-d", strtotime("+".(easter_days($yr) - 2). " days", strtotime("$yr-03-21 12:00:00") ));


			// Memorial Day  - always last Monday in May
			switch ( date("w", strtotime("$yr-05-31 12:00:00")) ) 
			{
                case 0: $bankHolidays[] = "$yr-05-25";
                    break;
                case 1: $bankHolidays[] = "$yr-05-31";
                    break;
                case 2: $bankHolidays[] = "$yr-05-30";
                    break;
                case 3: $bankHolidays[] = "$yr-05-29";
                    break;
                case 4: $bankHolidays[] = "$yr-05-28";
                    break;
                case 5: $bankHolidays[] = "$yr-05-27";
                    break;
                case 6: $bankHolidays[] = "$yr-05-26";
                    break;
			}
			
			
			// Juneteenth - alway June 19th
			// when falls on Sat - observe on Fri, when on Sun - observed Mon
			switch ( date("w", strtotime("$yr-06-16 12:00:00")) )
			{
			    case 0: $bankHolidays[] = "$yr-06-20"; 
			         break;
                case 6: $bankHolidays[] = "$yr-06-18";
                    break;
                default: $bankHolidays[] = "$yr-06-19";
			}


			// Independence Day(US) - alway observed on July 4th unless weekend
			// then its the next Monday (2021, 2026, 2027)
			if($yr == 2021) { // exception year	
			    $bankHolidays[] = "2021-07-05";
			    
			} else {
			    switch ( date("w", strtotime("$yr-07-04 12:00:00")) ) {
			        case 0: $bankHolidays[] = "$yr-07-05";
                        break;
                    case 6: $bankHolidays[] = "$yr-07-03";
                        break;
                    default: $bankHolidays[] = "$yr-07-04";
                }
			}


            // Labor Day - first Monday in September
            switch ( date("w", strtotime("$yr-09-01 12:00:00")) ) 
            {
                case 0: $bankHolidays[] = "$yr-09-01";
                    break;
			    case 1: $bankHolidays[] = "$yr-09-01";
			         break;
                case 2: $bankHolidays[] = "$yr-09-07";
                    break;
                case 3: $bankHolidays[] = "$yr-09-06";
                    break;
                case 4: $bankHolidays[] = "$yr-09-05";
                    break;
                case 5: $bankHolidays[] = "$yr-09-04";
				    break;
                case 6: $bankHolidays[] = "$yr-09-02";
                    break;
			}
			
			
			// Columbus Day - floating Monday
			switch ( date("w", strtotime("$yr-10-15 12:00:00")) ) 
			{
			    case 0: $bankHolidays[] = "$yr-10-09";
                    break;
                case 1: $bankHolidays[] = "$yr-10-10";
                    break;
                case 2: $bankHolidays[] = "$yr-10-14";
                    break;
                case 3: $bankHolidays[] = "$yr-10-15";
                    break;
                case 4: $bankHolidays[] = "$yr-10-12";
                    break;
                case 5: $bankHolidays[] = "$yr-10-11";
                    break;
                case 6: $bankHolidays[] = "$yr-10-10";
                    break;
			}


			// Veterans Day - always the 11th,
			// observed the next Monday if landing on weekend
			switch ( date("w", strtotime("$yr-11-11 12:00:00")) ) 
			{
                case 0: $bankHolidays[] = "$yr-11-12";
                    break;
                case 6: $bankHolidays[] = "$yr-11-10";
                    break;
				default: $bankHolidays[] = "$yr-11-11";
			}


			// Thanksgiving - floating Thurday in November
			switch ( date("w", strtotime("$yr-11-22 12:00:00")) ) 
			{
			    case 0: $bankHolidays[] = "$yr-11-26";
                    break;
                case 1: $bankHolidays[] = "$yr-11-25";
                    break;
                case 2: $bankHolidays[] = "$yr-11-24";
                    break;
                case 3: $bankHolidays[] = "$yr-11-23";
                    break;
                case 4: $bankHolidays[] = "$yr-11-24";
                    break;
                case 5: $bankHolidays[] = "$yr-11-28";
                    break;
                case 6: $bankHolidays[] = "$yr-11-27";
                    break;
            }


			// Christmas Day - always the 25th
			// if falls weekend, observed day before (sat), or following  Mon (sun)
			switch ( date("w", strtotime("$yr-12-25 12:00:00")) ) 
			{
                case 0: $bankHolidays[] = "$yr-12-26";
                    break;
                case 6: $bankHolidays[] = "$yr-12-24";
                    break;
				default: $bankHolidays[] = "$yr-12-25";
            }
            
            // test output for a given year
			// var_dump($bankHolidays);
			return $bankHolidays;
			
		}

}

// $cal = new CalculateBankHolidays();
