<?php

	$a = array(
		"https://youpic.com/photographer/Szuszkiewicz/",
		"https://youpic.com/photographer/jatphotography/",
		"https://youpic.com/photographer/lachowicz/ :)",
		"https://youpic.com/photographer/aninhasdemacedo/",
		"I think the link is https://youpic.com/photographer/ExperiencedTraveller/ but I have had a lot of error messages while trying to set everything up.",
		"Hey, Here is my profile: https://youpic.com/photographer/Kavakphotography/",
		"https://youpic.com/photographer/aninhasdemacedo/",
		"https://youpic.com/photographer/aninhasdemacedo/",
		"https://youpic.com/photographer/TheDutchPhotographer/",
		"https://youpic.com/photographer/ajaysolanki90/",
		"https://youpic.com/photographer/miriamrusin/",
		"https://youpic.com/photographer/FabioSozza/",
		"done :-)<br/>https://youpic.com/photographer/FabienPrauss/",
		"https://youpic.com/FrederickSchiele/ I just started a page.",
		"Just created an account. I will be more loading photos in the next days. thanks again. Nice<br/>https://youpic.com/photographer/PriscaTozzi/",
		"https://youpic.com/photographer/SarahMezger/",
		"https://youpic.com/photographer/mortenvilhelmkeller/<br/>Warm regards<br/>Morten",
		"https://youpic.com/DavideCapucci",
		"https://youpic.com/CliveEariss8",
		"https://youpic.com/photographer/BenAllen/<br/><br/>all set up now!",
		"For some reason on the laptop the link wouldn’t copy so I’ve sent a link via the app to all contacts on linkin. But in case it doesn’t come through here is the typed version<br/>https://youpic.com/DavidAlbutt",
		"Hello! my account with photos: https://youpic.com/photographer/Andrea_Passon/<br/><br/>i’m waiting for your review and a cover in home page, thank you",
		"https://youpic.com/photographer/SianPG/",
		"https://youpic.com/photographer/AndrewLever/",
		"like I thought , I was already on this platform <br/>https://youpic.com/photographer/daviddoylearts/",
		"https://youpic.com/photographer/kielphoto/",
		"https://youpic.com/photographer/niklasborsting/",
		"https://youpic.com/photographer/MalRenault/",
		"Here it is: https://youpic.com/photographer/KagLoos/",
		"Think you could benefit to upgrade to Premium then. We have BBC, WSJ and Business Insider scouting daily on YouPic. You would make back the money easily.  Here is what you will get www.youpic.com/pricing",
		"here we are!<br/>https://youpic.com/photographer/SamiraZuabiGarca/",
		"Awesome! I've uploaded a range of shots to begin with to my account, feel free to share!<br/><br/>https://youpic.com/photographer/MevansPhotography/",
		"All done! Here is my portfolio so far. Look good? :) <br/><br/>https://youpic.com/photographer/Studio204/",
		"Welcome. Here is my work, if you are interested: https://youpic.com/photographer/AliciaLindstrom/",
		"ok done<br/>https://youpic.com/photographer/NestorCorrea/",
	);

	foreach ($a as $key => $value) {
		$name = explode('youpic.com/photographer/', $value)[1] ?? explode('youpic.com/', $value)[1];
		$name = explode('/', $name)[0];
		echo $name.'<br/>';
	}