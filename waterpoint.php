<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Water Point Test - Victoria Odingo</title>
</head>

<body>
<?php
class WaterPoints {
 const jURL = "https://raw.githubusercontent.com/onaio/ona-tech/master/data/water_points.json";

//Retrieve the dataset from given json URL by calling the class retrievedata
$jdata = retrievedata(jURL);

if (json_decode($jdata, TRUE)) {
// pass true to convert the object to associative arrays
    $dataarray = json_decode($jdata, TRUE);

    //Actuall function call to do the calculations	
    $result = Calculate($dataarray);
    
    //print out the results received from the Calculate function 
    var_dump($calc);
}

public function retrievedata($url) {
 //Initialize the cURL session by creating a new cURL resource handle
 $ch = curl_init();
 // set URL and other cURL options
$options = array(CURLOPT_URL => jURL,
                 CURLOPT_HEADER => false,
				 CURLOPT_USERAGENT => USAGE,
				 CURLOPT_RETURNTRANSFER => True
				 CURLOPT_HEADER => 0
                );
curl_setopt_array($ch, $options);
 // grab the URL and Execute the cURL session
$output = curl_exec($ch);

 // close cURL resource, and free up system resources
        curl_close($ch);
        return $output;
} //closes function retrievedata

 public function Calculate($decoded_data) { //main function
        $returncountvalues = array(); //return values after calulation
		$water_counter = 0; //holds count of functional water points
		$community_name_array = array(); //holds the communities in an array
        $community_list = array(); //water points per community list
        $community_brokenwater=array(); //holds communities and number of broken waterpoints
		
       
        foreach ($decoded_data as $data => $value) { //retrieve data from the passed array
            if (isset($value['water_functioning'])) {
                $waterpoint_status = $value['water_functioning']; //pass the value of the water_functioning column to variable
				if (strcasecmp($waterpoint_status, "yes") == 0) { //compare the string values and filter for 'yes' values
                    $water_counter = $water_counter + 1;
                }
				            
            }
        }


		foreach ($decoded_data as $data => $value) {
            if (isset($value['communities_villages'])) {               
                $community = $value['communities_villages'];
                if (!in_array($community, $community_name_array)) {
                    array_push($community_name_array, $community);
                }              
             }
        }


        /*The for loop loops through every community retrievd from the community_name_array and uses the values to calculate total and broken water points per community */ 
        for ($i = 0; $i < count($community_name_array); $i++) { 
            $comm_name = $community_name_array[$i];
			
            $community_list[$comm_name] = WaterPointsPerCommunity($decoded_data, $comm_name); //gets the number of water points per community and stores in an array
            
            $community_brokenwater[$comm_name] = BrokenWaterPoints($decoded_data, $comm_name); //gets the total broken water point per community
        }
        
		/*This code section gets the communities with broken water points, calculates percentages and ranks them.
		(a) First we get the total number of broken water points from all communities.
		(b) We then use the formula (number of broken waterpoint per community * 100 /total broken water points) to calculate percentage per community
		(c) Finally we sort the percentages as ranks.*/
	
        //(a)
		$totalbroken=0;
        foreach ($community_brokenwater as $community=>$brokenwatervalue){
            $totalbroken=$totalbroken+$brokenwatervalue;
        }
        //(b)
        $communitypercentage = array();
        foreach ($community_brokenwater as $community=>$brokenwatervalue){
            $calc_percentage = ($brokenwatervalue*100)/$totalbroken;
            $communitypercentage[$community]=  $calc_percentage;
        }
        //(c)
       $communitypercentageSorted = array_multisort($communitypercentage);
     	
		
	    // Return results as multi dimensional array          
        $returncountvalues['The number of waterpoints functional:'] = $water_counter;
        $returncountvalues['The number of waterpoints/community|:'] = $community_list;
        $returncountvalues['Ranking:'] = communitypercentageSorted;

        return $returncountvalues;
    }// ends the calculate function



 public function WaterPointsPerCommunity($decoded_data, $community) { //function that accepts the dataset and community name as parameters to get water points total whether functioning or not
        $counter = 0;
        foreach ($decoded_data as $data => $value) { //retrieves data from dataset
            if (isset($value['communities_villages']) && isset($value['water_functioning'])) { //checks each record to ensure each community has a corresponding water point
                $commun = $value['communities_villages']; //passes the community name to variable
                if (strcasecmp($community, $commun) == 0) { //compares the passed community to the retrieved community if they are similar
                    $counter = $counter + 1;
                }
            }
        }
        return $counter;
    }// ends the WaterPointsPerCommunity function
	

 public function BrokenWaterPoints($decoded_data, $community) {
        $counter = 0;
        foreach ($decoded_data as $data => $value) {
            if (isset($value['communities_villages'])) {
                $commun = $value['communities_villages'];
                if (strcasecmp($community, $commun) == 0) {
                    if(isset($value['water_point_condition'])){
                        $broken = $value['water_point_condition'];
                        if (strcasecmp($broken, "broken") == 0) {
                            $counter = $counter + 1;
                        }
                    }
                }
            }
        }
        return $counter;
    }// ends the BrokenWaterPoints function


}//closes WaterPoints class
?>

</body>
</html>
