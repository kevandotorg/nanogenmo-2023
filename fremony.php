<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="fremony.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Crimson+Pro:wght@600&family=Archivo+Narrow:wght@700&display=swap" rel="stylesheet"> 
<title>The Daily Fremony</title>
</head>
<body>

<h1>The Daily Fremony</h1>

<div class="date">30th November 2023</div>

<div class="intro">This a collection of digitally-altered <a href="https://newspapers.library.wales">public domain news stories</a> from
an age when the unreliable influence of news production machinery was generally visible to the reader. Generated for
<a href="https://github.com/NaNoGenMo/2023/">NaNoGenMo 2023</a> from <a href="https://github.com/kevandotorg/nanogenmo-2023">a script</a> by <a href="https://kevan.org/">Kevan Davis</a>,
random stories from 19th to early 20th century newspapers are fed through the Peterborough Standard's <a href="https://bravenewmalden.com/2011/02/03/the-fremony-at-the-library/">1979 fremonising process</a>.</div>

<?

$newstxt = file("news.txt"); 
$sportstxt = file("sports.txt"); 
$wordcount = 0;

while ($wordcount < 50000)
{
	
$remony = "";

while ($remony == "")
{
	// pick a news story and break it up

	$newsstory = "";
	while (!anygood($newsstory))
	{ $newsstory = $newstxt[rand(0,count($newstxt)-1)]; }
	$newsstory = demisterfy($newsstory);
	$newslines = explode(". ",$newsstory);
	foreach ($newslines as &$line) { $line .= "."; }
	//print_r($newslines);
	
	if (sizeof($newslines)>2)
	{	
		$outlines = array();

		// pick a sports story and get a quote from it

		$sportquote = "";
		while (strlen($sportquote)<60 || strlen($sportquote)>421)
		{
			$sportstory = $sportstxt[rand(0,count($sportstxt)-1)];
			$sportstory = demisterfy($sportstory);

			$sportlines = explode(". ",$sportstory);
			array_filter($sportlines);

			//print_r($sportlines);
			$sporti = rand(0,sizeof($sportlines)-3);
			$sportquote = $sportlines[$sporti].".</p><p>".$sportlines[$sporti+1].".</p><p>".$sportlines[$sporti+2].".";
			$sportquote = preg_replace("/\.\.$/",".",$sportquote);
			$sportquote = substr($sportquote,min(rand(4,30),strlen($sportlines[$sporti])*.8));
		}
		
		// locate the "remony at the library." fragment for the paragraph; 3+ words from the end, ideally as few as possible;
		// breaking a word at 5+ characters and ideally on a break letter which can follow most consonants.

		$remony="";

		// try for an R
		for ($i=2; $i<6; $i++)
		{
			if ($remony == "" && preg_match("/\S\S([r]\w{4,}( \w+){".$i."}\.)/", $newslines[1], $matches))
			{ $remony = $matches[1]; }
		}

		// H or L will do in a pinch
		for ($i=2; $i<6; $i++)
		{
			if ($remony == "" && preg_match("/\S\S([hl]\w{4,}( \w+){".$i."}\.)/", $newslines[1], $matches))
			{ $remony = $matches[1]; }
		}

		// otherwise, anything
		for ($i=2; $i<6; $i++)
		{
			if ($remony == "" && preg_match("/\S\S(\w{5,}( \w+){".$i."}\.)/", $newslines[1], $matches))
			{ $remony = $matches[1]; }
		}

		//print "\nremony = $remony\n";
	}
	
	// bail if the headline isn't all caps, or if the first line of the story is still part of the headline, or too short, or has obvious OCR junk
	if ($newslines[0] != strtoupper($newslines[0])) { $remony = ""; }
	if ($newslines[1] == strtoupper($newslines[1])) { $remony = ""; }
	if (strlen($newslines[1])<80) { $remony = ""; }
	if (preg_match("/(\^|<\?)/",$newslines[1])) { $remony = ""; }
}

// original headline

array_push($outlines,$newslines[0]);

// first line is unchanged

array_push($outlines,$newslines[1]);

// second line ideally switches into $remony four characters later than it was reached in the first
// line; if the second line is shorter, break it 90% of the way through

$rempos = strpos($newslines[1],$remony);
$newpos = $rempos+4;
if ($newpos > strlen($newslines[2]))
{ $newpos = ceil(strlen($newslines[2]) * 0.9); }
$secondlinestart = substr($newslines[2],0,$newpos);

array_push($outlines,$secondlinestart.$remony);

// third sentence repeats second but breaks 42 characters earlier; if that can't be done, break
// it 75% earlier.
// ... then it drops 9 letters from the end and repeats, then drops 8 letters and repeats.

$newpos = $rempos-42;
if ($newpos < 0)
{ $newpos = ceil($rempos*.75); }
$newline = substr($newslines[2],0,$newpos).$remony;
$newline = substr($newline,0,-9).$remony;
$newline = substr($newline,0,-8).$remony;
array_push($outlines,$newline);

// fourth sentence is just seven characters and a remony

array_push($outlines,substr($newslines[2],0,7).$remony);

// fifth sentence is one character and a remony, then dropping 10 and a remony

$newline = substr($newslines[2],0,1).$remony;
$newline = substr($newline,0,-10	).$remony;
array_push($outlines,$newline);

// sixth sentence is two characters, first letter of a remony, remony-11, remony-6, remony-3, remony

array_push($outlines,substr($newslines[2],0,2).firstchar($remony).endcut($remony,11).endcut($remony,6).endcut($remony,3).$remony);

// seventh sentence is three characters then a remony

array_push($outlines,substr($newslines[2],0,3).$remony);

// eighth sentence is 47 characters and the first word of a remony, first letter, remony-6, remony-14, -5, -13, -11, and a remony

array_push($outlines,substr($newslines[2],0,47).firstword($remony)." ".firstchar($remony).endcut($remony,6).endcut($remony,14).endcut($remony,5).endcut($remony,13).endcut($remony,11).$remony);

// ninth sentence is one character, remony+4, remony+1, then two full remonies

array_push($outlines,substr($newslines[2],0,1).startcut($remony,4).startcut($remony,1).$remony.$remony);

// tenth reuses the second line start and switches to sports news

array_push($outlines,$secondlinestart.$sportquote);

// output

foreach ($outlines as &$countline)
{
	$countline = preg_replace("/&period([^;])/", "$1", $countline); // brush away any broken, escaped full stops
	$countline = preg_replace("/&perio([^d])/", "$1", $countline); // brush away any broken, escaped full stops
	$countline = preg_replace("/&peri([^o])/", "$1", $countline); // brush away any broken, escaped full stops
	$countline = preg_replace("/&per([^i])/", "$1", $countline); // brush away any broken, escaped full stops
	$countline = preg_replace("/&pe([^r])/", "$1", $countline); // brush away any broken, escaped full stops
	$countline = preg_replace("/&p([^e])/", "$1", $countline); // brush away any broken, escaped full stops
	$countline = preg_replace("/^-/", "", $countline);
	$wordcount += str_word_count($countline);
}

$outlines[1] = preg_replace_callback("/^([a-z]+)\b/",'paraback',$outlines[1]);

$outlines[0] = trim($outlines[0],".");
$outlines[0] = preg_replace("/^I+ /","",$outlines[0]);

$outlines[1] = trim($outlines[1],"-");

?>
<h2><?=headlinecase($outlines[0])?></h2>
<div class="story">
<p><?=$outlines[1]?></p>
<p><?=$outlines[2]?></p>
<p><?=$outlines[3]?></p>
<p><?=$outlines[4]?></p>
<p><?=$outlines[5]?></p>
<p><?=$outlines[6]?></p>
<p><?=$outlines[7]?></p>
<p><?=$outlines[8]?></p>
<p><?=$outlines[9]?></p>
<p><?=$outlines[10]?></p>
</div>

<?

}

function startcut($string,$cut)
{
	return substr($string,0,$cut);
}

function endcut($string,$cut)
{
	return substr($string,0,-$cut);
}

function firstchar($string)
{
	return substr($string,0,1);
}

function firstword($string)
{
	return explode(' ', trim($string))[0];
}

function anygood($string)
{
	if (preg_match("/^[A-Z ]+\..+\.+/",$string))
	{ return true; }
	return false;
}

function demisterfy($string)
{
	$string = preg_replace("/([A-Z][a-z])\./","$1&period;",$string);
	$string = preg_replace("/\b([A-Z][A-Z])\./","$1&period;",$string);
	$string = preg_replace("/(Mrs|Gen|Hon|Esq|Messrs|Rev)\./","$1&period;",$string);
	$string = preg_replace("/([a-z])[-\.] ([a-z])/","$1$2",$string);
	$string = preg_replace("/ ([A-Z])\./","$1&period;",$string);
	$string = preg_replace("/^([A-Z]+)\./","$1&period;",$string);
	$string = preg_replace("/([0-9]+[lsd]?)\./","$1&period;",$string);
	$string = preg_replace("/\.-/",". -",$string);

	// OCR quirks
	$string = preg_replace("/-[-\+—♦]+/","",$string);
	$string = preg_replace("/ [\+]+ /"," ",$string);
	$string = preg_replace("/[♦■]/"," ",$string);
	$string = preg_replace("/\.,/",". ",$string);

	$string = trim($string);
	return $string;
}

function headlinecase($string)
{
	$string = ucfirst(strtolower($string));

	$string = preg_replace_callback("/\b(afghanistan|albania|algeria|america|andorra|angola|antigua and barbuda|argentina|armenia|australia|austria|azerbaijan|the bahamas|bahrain|bangladesh|barbados|belarus|belgium|belize|benin|bhutan|bolivia|bosnia and herzegovina|botswana|brazil|britain|brunei|bulgaria|burkina faso|burundi|cabo verde|cambodia|cameroon|canada|central african republic|chad|chile|china|colombia|comoros|congo, democratic republic of the|congo, republic of the|costa rica|côte d’ivoire|croatia|cuba|cyprus|czech republic|denmark|djibouti|dominica|dominican republic|east timor (timor-leste)|ecuador|egypt|el salvador|equatorial guinea|eritrea|estonia|eswatini|ethiopia|fiji|finland|france|gabon|the gambia|georgia|germany|ghana|greece|grenada|guatemala|guinea|guinea-bissau|guyana|haiti|honduras|hungary|iceland|india|indonesia|iran|iraq|ireland|israel|italy|jamaica|japan|jordan|kazakhstan|kenya|kiribati|korea, north|korea, south|kosovo|kuwait|kyrgyzstan|laos|latvia|lebanon|lesotho|liberia|libya|liechtenstein|lithuania|luxembourg|madagascar|malawi|malaysia|maldives|mali|malta|marshall islands|mauritania|mauritius|mexico|micronesia, federated states of|moldova|monaco|mongolia|montenegro|morocco|mozambique|myanmar (burma)|namibia|nauru|nepal|netherlands|new zealand|nicaragua|niger|nigeria|north macedonia|norway|oman|pakistan|palau|panama|papua new guinea|paraguay|peru|philippines|poland|portugal|qatar|romania|russia|rwanda|saint kitts and nevis|saint lucia|saint vincent and the grenadines|samoa|san marino|sao tome and principe|saudi arabia|scotland|senegal|serbia|seychelles|sierra leone|singapore|slovakia|slovenia|solomon islands|somalia|south africa|spain|sri lanka|sudan|suriname|sweden|switzerland|syria|taiwan|tajikistan|tanzania|thailand|togo|tonga|trinidad and tobago|tunisia|turkey|turkmenistan|tuvalu|uganda|ukraine|united arab emirates|united kingdom|united states|uruguay|uzbekistan|vanuatu|vatican city|venezuela|vietnam|wales|yemen|zambia|zimbabwe)\b/","caseback",$string);
	$string = preg_replace_callback("/\b(aberdare|abergavenny|abertillery|aberystwyth|adur|adwick le street|allerdale|alloway|alness|alton|amber valley|amersham|amesbury|ampthill|andover|antrim|arbroath|armagh|arun|arundel|ascot|ashbourne|ashburton|ashfield|ashford|atherton|axminster|aylesbury|aylesbury vale|ayr|babergh|badminton|bala|ballycastle|ballymena|ballymoney|balquhidder|bamburgh|banbridge|banbury|banff|bangor|bannockburn|barking and dagenham|barnard castle|barnet|barnsley|barnstaple|barrow-in-furness|barry|basildon|basingstoke and deane|bassetlaw|battersea|battle|beaconsfield|beccles|bedford|bedlington|bedworth|beeston and stapleford|belper|berkhamsted|beverley|bexhill|bexley|bicester|bideford|birkenhead|birmingham|bishop’s stortford|blaby|bloomsbury|bodmin|bognor regis|bolsover|bolton|boston|bradford|bradford-on-avon|braemar|braintree|bray|brechin|breckland|brecon|brent|brentwood|bridgend|bridgnorth|bridgwater|brighton|brixham|broadland|broadstairs and st. peter’s|broadway|bromley|bromsgrove|broxbourne|broxtowe|buckhaven|builth wells|burford|burnham-on-crouch|burnley|burton upon trent|bury|bury st. edmunds|buxton|caerleon|caernarfon|caerphilly|calderdale|callander|cambridge|camden|campbeltown|cannock chase|canterbury|cardigan|carisbrooke|carlisle|carmarthen|carrickfergus|castle point|castle rising|cawdor|chalfont st. giles|charing cross|charnwood|chatham|cheddar|chelmsford|cheltenham|chepstow|cherwell|chester|chester-le-street|chesterfield|chichester|chigwell|chiltern|chippenham|chorley|christchurch|cirencester|city of london|city of westminster|cleethorpes|clerkenwell|clydebank|coatbridge|cockermouth|colchester|coldstream|coleraine|colwyn bay|congleton|conwy|cookstown|copeland|corby|corfe castle|cotswold|coventry|cowbridge|cowes|craigavon|cramlington|craven|crawley|crediton|crewe|cricklade|cromarty|crowborough|crowland|croydon|cruden bay|culross|cumbernauld|cumnock|cupar|cwmbrân|dacorum|dalkeith|dartford|dartmouth|daventry|dawlish|deal|denbigh|derbyshire dales|devizes|dewsbury|doncaster|dorchester|dorking|dover|downpatrick|droitwich|dromore|dudley|dulwich|dumbarton|dumfries|dunbar|dunfermline|dungannon|dunkeld|dunoon|duns|dunstable|dunster|dunwich|durham|ealing|east cambridgeshire|east dereham|east devon|east dorset|east grinstead|east hampshire|east hertfordshire|east kilbride|east lindsey|east northamptonshire|east staffordshire|eastbourne|eastleigh|ebbw vale|eden|edenbridge|elgin|elmbridge|ely|enfield|enniskillen|epping forest|epsom and ewell|erewash|eton|evesham|exeter|exmouth|falkirk|falmouth|fareham|faversham|felixstowe|felling|fenland|folkestone|forest heath|forest of dean|forfar|forres|fort william|fowey|freshwater|fylde|gainsborough|galashiels|gateshead|gedling|gelligaer|gillingham|glamis|glastonbury|glenrothes|gloucester|goole|gosport|grangemouth|grantham|grasmere|gravesend|gravesham|great malvern|great yarmouth|greenock|greenwich|gretna green|grimsby|guildford|hackney|haddington|halifax|hambleton|hamilton|hammersmith and fulham|harborough|haringey|harlech|harlow|harrogate|harrow|hart|harwich|haslemere|hastings|hatfield|havant|haverfordwest|havering|hawarden|hawick|haworth|helston|hemel hempstead|henley-on-thames|hereford|herne bay|herstmonceux|hertford|hertsmere|hexham|high peak|high wycombe|hillingdon|hinckley and bosworth|hirwaun|holyhead|holywell|horsham|hounslow|hove|huddersfield|hugh town|huntingdon|huntingdonshire|huyton|hyndburn|hythe|ilchester|inner london|inveraray|invergordon|inverness|ipswich|irvine|islington|jarrow|jedburgh|john o’groats|keighley|kelso|kendal|kensington and chelsea|keswick|kettering|kidderminster|kilkeel|kilmarnock|king’s lynn|king’s lynn and west norfolk|kingston upon thames|kingswood|kinross|kirkcaldy|kirkcudbright|kirkintilloch|kirklees|kirkwall|knaresborough|knowsley|knutsford|lambeth|lanark|lancaster|langport|larne|launceston|leeds|leith|leominster|lerwick|letchworth|lewes|lewisham|lichfield|limavady|limehouse|lincoln|linlithgow|lisburn|liverpool|livingston|llandaff|llandrindod wells|llandudno|llanelli|llangefni|llantrisant|llantwit major|lochgilphead|lochmaben|londonderry|looe|lossiemouth|lostwithiel|loughborough|lowestoft|ludlow|lurgan|lydd|lyme regis|lynton and lynmouth|macclesfield|magherafelt|maidenhead|maidstone|maldon|malmesbury|malton|malvern hills|manchester|mansfield|margam|margate|market harborough|marlborough|marlow|matlock|mauchline|melrose|melton|mendip|merton|mid devon|mid suffolk|mid sussex|mildenhall|milford haven|milngavie|minehead|mole valley|monmouth|montgomery|montrose|morpeth|motherwell and wishaw|mountain ash|much wenlock|nantwich|neath|new forest|new romney|newark and sherwood|newark-on-trent|newburn|newbury|newcastle|newcastle upon tyne|newcastle-under-lyme|newham|newhaven|newmarket|newport|newquay|newry|newton abbot|newtown|newtown st. boswells|newtownabbey|newtownards|nigg|north devon|north dorset|north east derbyshire|north hertfordshire|north kesteven|north norfolk|north tyneside|north warwickshire|north west leicestershire|northallerton|northampton|northwich|norwich|nuneaton and bedworth|oadby and wigston|okehampton|oldham|omagh|oswestry|oundle|outer london|oxford|paisley|peebles|pembroke|pendle|penrith|penryn|penzance|perth|peterhead|petworth|pevensey|pontardawe|pontefract|pontypool|pontypridd|port talbot|porthcawl|portrush|preston|prestwick|purbeck|ramsey|ramsgate|redbridge|redditch|reigate and banstead|renfrew|repton|rhyl|ribble valley|richmond|richmond upon thames|richmondshire|ripon|rochdale|rochester|rochford|romsey|ross-on-wye|rossendale|rosyth|rother|rotherham|rothesay|royal leamington spa|royal tunbridge wells|rugby|runcorn|runnymede|rushcliffe|rushmoor|ryde|rye|ryedale|saffron walden|salford|salisbury|saltaire|saltash|sandhurst|sandringham|sandwell|sandwich|scarborough|scone|scunthorpe|sedgemoor|sefton|selby|selkirk|sevenoaks|sheffield|shepway|shoreham-by-sea|shrewsbury|sidmouth|silchester|skelmersdale|smithfield|soho|solihull|south bucks|south cambridgeshire|south derbyshire|south hams|south holland|south kesteven|south lakeland|south norfolk|south northamptonshire|south oxfordshire|south ribble|south shields|south somerset|south staffordshire|south tyneside|southport|southwark|spelthorne|st. albans|st. andrews|st. asaph|st. austell|st. david’s|st. edmundsbury|st. fergus|st. helens|st. ives|st. marylebone|stafford|staffordshire moorlands|staines|stamford|stevenage|stirling|stockport|stoke poges|stokesay|stormont|stornoway|strabane|stratford-on-avon|stroud|sudbury|suffolk coastal|sullom voe|sunderland|surrey heath|sutton|swale|swansea|tameside|tamworth|tandridge|tarbert|taunton|taunton deane|teddington|teignbridge|teignmouth|telford|tenby|tendring|test valley|tewkesbury|thanet|thetford|three rivers|thurso|tilbury|tintagel|todmorden|tonbridge and malling|torridge|totnes|tower hamlets|trafford|trowbridge|truro|tunbridge wells|uppingham|usk|uttlesford|vale of white horse|vauxhall|ventnor|wakefield|wallsend|walsall|waltham forest|walton-le-dale|wandsworth|wantage|ware|warkworth|warwick|washington|watford|waveney|waverley|wealden|wellingborough|wellington|wells|welshpool|welwyn garden city|welwyn hatfield|west bridgford|west bromwich|west devon|west dorset|west lancashire|west lindsey|west oxfordshire|west somerset|westbury|weston-super-mare|weymouth and portland|whitby|whitehaven|whithorn|whitstable|wick|widnes|wigan|wilton|wimbledon|wimborne minster|winchcombe|winchelsea|winchester|windsor|wirral|wisbech|woking|wolverhampton|woodbridge|woolwich|worcester|workington|worksop|worthing|wrexham|wychavon|wycombe|wyre|wyre forest|edinburgh|glasgow|dundee|aberdeen|inverness|perth|stirling|dunfermline|bangor|cardiff|newport|st asaph|st davids|swansea|dublin|limerick|waterford|cork|galway|kilkenny|derry|belfast|armagh|newry|lisburn)\b/","caseback",$string);
	$string = preg_replace_callback("/\b(afghan|albanian|algerian|american|andorran|angolan|anguillan|citizen of antigua and barbuda|argentine|armenian|australian|austrian|azerbaijani|bahamian|bahraini|bangladeshi|barbadian|belarusian|belgian|belizean|beninese|bermudian|bhutanese|bolivian|citizen of bosnia and herzegovina|botswanan|brazilian|british|british virgin islander|bruneian|bulgarian|burkinan|burmese|burundian|cambodian|cameroonian|canadian|cape verdean|cayman islander|central african|chadian|chilean|chinese|colombian|comoran|congolese (congo)|congolese (drc)|cook islander|costa rican|croatian|cuban|cymraes|cymro|cypriot|czech|danish|djiboutian|dominican|citizen of the dominican republic|dutch|east timorese|ecuadorean|egyptian|emirati|english|equatorial guinean|eritrean|estonian|ethiopian|faroese|fijian|filipino|finnish|french|gabonese|gambian|georgian|german|ghanaian|gibraltarian|greek|greenlandic|grenadian|guamanian|guatemalan|citizen of guinea-bissau|guinean|guyanese|haitian|honduran|hong konger|hungarian|icelandic|indian|indonesian|iranian|iraqi|irish|israeli|italian|ivorian|jamaican|japanese|jordanian|kazakh|kenyan|kittitian|citizen of kiribati|kosovan|kuwaiti|kyrgyz|lao|latvian|lebanese|liberian|libyan|liechtenstein citizen|lithuanian|luxembourger|macanese|macedonian|malagasy|malawian|malaysian|maldivian|malian|maltese|marshallese|martiniquais|mauritanian|mauritian|mexican|micronesian|moldovan|monegasque|mongolian|montenegrin|montserratian|moroccan|mosotho|mozambican|namibian|nauruan|nepalese|new zealander|nicaraguan|nigerian|nigerien|niuean|north korean|northern irish|norwegian|omani|pakistani|palauan|palestinian|panamanian|papua new guinean|paraguayan|peruvian|pitcairn islander|polish|portuguese|prydeinig|puerto rican|qatarir|romanian|russian|rwandan|salvadorean|sammarinese|samoan|sao tomean|saudi arabian|scottish|senegalese|serbian|citizen of seychelles|sierra leonean|singaporean|slovak|slovenian|solomon islander|somali|south african|south korean|south sudanese|spanish|sri lankan|st helenian|st lucian|stateless|sudanese|surinamese|swazi|swedish|swiss|syrian|taiwanese|tajik|tanzanian|thai|togolese|tongan|trinidadian|tristanian|tunisian|turkish|turkmen|turks and caicos islander|tuvaluan|ugandan|ukrainian|uruguayan|uzbek|vatican citizen|citizen of vanuatu|venezuelan|vietnamese|vincentian|wallisian|welsh|yemeni|zambian|zimbabwean)/","caseback",$string);
	$string = preg_replace_callback("/\b(aberdeenshire|anglesey|argyll|avon|ayrshire|banffshire|bedford|bedfordshire|berkshire|berwickshire|blackpool|bournemouth|brecknockshire|bristol|buckinghamshire|bute|caernarfonshire|caithness|cambridgeshire|cardiganshire|carmarthenshire|cheshire|clackmannanshire|cleveland|clwyd|cornwall|cromartyshire|cumberland|cumbria|darlington|denbighshire|derby|derbyshire|devon|dorset|dumfriesshire|dyfed|essex|fife|flintshire|glamorgan|gloucestershire|gwent|gwynedd|halton|hampshire|hartlepool|herefordshire|hertfordshire|humberside|huntingdonshire|inverness-shire|kent|kincardineshire|kinross-shire|kirkcudbrightshire|lanarkshire|lancashire|leicester|leicestershire|lincolnshire|london|luton|medway|merionethshire|merseyside|middlesbrough|middlesex|monmouthshire|montgomeryshire|nairnshire|norfolk|northamptonshire|northumberland|nottingham|nottinghamshire|orkney|oxfordshire|peeblesshire|pembrokeshire|perthshire|peterborough|plymouth|poole|portsmouth|powys|radnorshire|renfrewshire|ross-shire|roxburghshire|rutland|selkirkshire|shropshire|somerset|southampton|southend-on-sea|staffordshire|stirlingshire|stockton-on-tees|stoke-on-trent|suffolk|surrey|sussex|sutherland|swindon|thurrock|torbay|warrington|warwickshire|westmorland|wigtownshire |wiltshire|worcestershire|wrexham |york|yorkshire)\b/","caseback",$string);
	$string = preg_replace_callback("/\b(monday|tuesday|wednesday|thursday|friday|saturday)\b/","caseback",$string);

	return $string;
}

function caseback($match) {
    return ucwords($match[0]);
}
function paraback($match) {
    return strtoupper($match[0]);
}

?>

</body></html>