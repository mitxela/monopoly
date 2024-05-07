<?php require('redirect.php'); 

$allThemes='["UK Classic", "US Classic","Futurama", "The Godfather"]';

//'["#964818","#00A6EC","#E50083","#F28F00","#E50005","#FFEE00","#019837","#004D9F"]';

//Defaults
$colours='["#934725","#BCDFF2","#D72C88","#F29104","#E11919","#FCDB10","#3C934C","#0769B2"]';
$moneyUnit="&pound;";
$buildings='["house","houses","hotel","hotels"]';
$tokens='["Wheelbarrow","Battleship","Racecar","Thimble","Boot","Scottie Dog","Top Hat","Man on Horseback","Iron","Howitzer"]';
$tokenImage='theme/Tokens.png';
$names='["Go","Old Kent Road","Community Chest","Whitechapel Road","Income Tax","King\'s Cross Station","The Angel Islington","Chance","Euston Road","Pentonville Road","Jail","Pall Mall","Electric Company","Whitehall","Northumberland Avenue","Marylebone Station","Bow Street","Community Chest","Marlborough Street","Vine Street","Free Parking","Strand","Chance","Fleet Street","Trafalgar Square","Fenchurch Street Station","Leicester Square","Coventry Street","Water Works","Piccadilly","Go to Jail","Regent Street","Oxford Street","Community Chest","Bond Street","Liverpool Street Station","Chance","Park Lane","Super Tax","Mayfair"]';
$boardImage="theme/UKboard.jpg";
$cardImages='["theme/Water.png","theme/Electric.png","theme/Train.png"]';


$theme=json_decode($allThemes);
switch ($theme[$game["Theme"]]) {

case "US Classic":
$moneyUnit="$";
$boardImage="theme/USboard.jpg";
$colours='["#590C38","#87A5D7","#F0377A","#F68124","#EE3A23","#FDE805","#13A55C","#294DA1"]';
$names='["Go","Mediterranean Avenue","Community Chest","Baltic Avenue","Income Tax","Reading Railroad","Oriental Avenue","Chance","Vermont Avenue","Connecticut Avenue","Jail","St. Charles Place","Electric Company","States Avenue","Virginia Avenue","Pennsylvania Railroad","St. James Place","Community Chest","Tennessee Avenue","New York Avenue","Free Parking","Kentucky Avenue","Chance","Indiana Avenue","Illinois Avenue","B&O Railroad","Atlantic Avenue","Ventnor Avenue","Water Works","Marvin Gardens","Go To Jail","Pacific Avenue","North Carolina Avenue","Community Chest","Pennsylvania Avenue","Short Line","Chance","Park Place","Luxury Tax","Boardwalk"]';
break;

case "The Godfather":
$moneyUnit="$";
$buildings='["hideout","hideouts","compound","compounds"]';
$names='["Go","Hyman Roth\'s Home","Friends","Havana Capri","Bribery","Fishing Boat","Clemenza\'s House","Enemies","Jones Beach Toll","Calvary Cemetery","Jail","Joe\'s Diner","NYPD","Jack Dempsey\'s","Louis Restaurant","Getaway Car","Moe Green\'s Casino","Friends","Woltz Intl. Pictures","Woltz Mansion","Free Parking","Don Ciccio Estate","Enemies","Corleone Apartment","Tommasino Estate","Delivery Truck","Sicilian Church","Old St. Patrick\'s","Politicians","St. Peter\'s Basilica","Go To Jail","Teatro Massimo","Abbandando Grosseria","Friends","Genco Import Co.","Passenger Train","Enemies","Corleone Estate","Legitimate Business Tax","Corleone Home"]';
$boardImage="theme/GodfatherBoard.jpg";
$tokens='["Limousine","Tommy Gun","Cannoli","Dead Fish","Olive Oil","Horse Head"]';
$tokenImage='theme/GodfatherTokens.png';
//card images
break;


case "Futurama":
$moneyUnit="$";
$buildings='["Resi-Dome","Resi-Domes","People Hive","People Hives"]';
$tokens='["Bender","Hypnotoad","Brain Slug","Seymour","Planet Express Ship","What If Machine"]';
$tokenImage='theme/FuturamaTokens.png';
$boardImage='theme/FuturamaBoard.jpg';
$names='["Go","Sewer City","Attention Puny Humans!","Cookieville Minimum-Security Orphanarium","Central Bureaucracy Tax","The Nimbus","Antares 3","Good News, Everyone!","Vergon 6","Amphibios 9","Jail","The Hip Joint","Westside Pipeway","Studio 1<sup>2</sup>2<sup>1</sup>3<sup>3</sup>","Elzar\'s Fine Cuisine","The MomCorp Flagship","Robot Arms Apartments","Attention Puny Humans!","HAL Institute for Criminally Insane Robots","Robot Hell","Free Parking","Santa\'s Workshop on Neptune","Good News, Everyone!","Lrrr\'s Palace on Omicron Persei 8","Snu Snu Chambers on Amazonia","Nibbler\'s Ship","Head Museum","Underground White House","Applied Cryogenics","Planet Express","Go To Jail","Future-Roma","Lost City of Atlanta","Attention Puny Humans!","Mars Vegas","Omicronian Mothership","Good News, Everyone!","Wong Ranch","League of Robots Dues","Mom\'s Friendly Robot Company"]';
//card images
break;

}



echo "data.iTh=$game[Theme];\nthemes=$allThemes;\nmoneyUnit='$moneyUnit';\nbuildings=$buildings;\ncardImg=$cardImages;\ncolours=$colours;\ntokens=$tokens;\nnames=$names;\n(img=new Image()).src='$boardImage';\n(tokenImg=new Image()).src=tokenImgSrc='$tokenImage';";
?>