<?php
function curPageURL()
{
	$pageUrl = $_SERVER["REQUEST_URI"];
	$page = explode("/", $pageUrl);
	$pos = strpos($page[1], 'index.php');
	if ($pos !== false) {
		$return = 'index.php';
	} else if ($page[2]) {
		$return = array($page[1], $page[2]);
	} else {
		$return = $page[1];
	}
	return $return;
}

define('IN_SITE', true);
error_reporting(1);

require_once('database_config.php');
require_once('charfunctions.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Open RSC</title>
	<meta name="description" content="Striving for a replica RSC game and more.">
	<meta name="keywords" content="openrsc,open rsc,rsc,open-rsc,rs classic,orsc evo,openrsc evolution">

	<!-- Bootstrap core CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css"
		  integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

	<!-- Javascript -->
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
			integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
			crossorigin="anonymous"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"
			integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut"
			crossorigin="anonymous"></script>
	<script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"
			integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k"
			crossorigin="anonymous"></script>
	<script src="../js/grayscale.min.js?v=1.0.0"></script>
	<script type="text/javascript" src="../js/twitterFetcher.min.js?v=1.0.0"></script>
	<script type="text/javascript" src="../js/jquery.mCustomScrollbar.concat.min.js?v=1.0.0"></script>
	<script type="text/javascript" src="../js/jquery.timeago.min.js?v=1.0.0"></script>
	<script>
		jQuery(document).ready(function () {
			jQuery("time.timeago").timeago();
		});

		jQuery(document).ready(function ($) {
			$(".clickable-row").click(function () {
				window.location = $(this).data("href");
			});
		});

		function search() {
			// Declare variables
			var input, filter, table, tr, td, i, txtValue;
			input = document.getElementById("inputBox");
			filter = input.value.toUpperCase();
			table = document.getElementById("itemList");
			tr = table.getElementsByTagName("tr");

			// Loop through all table rows, and hide those who don't match the search query
			for (i = 0; i < tr.length; i++) {
				td = tr[i].getElementsByTagName("td")[0];
				if (td) {
					txtValue = td.textContent || td.innerText;
					if (txtValue.toUpperCase().indexOf(filter) > -1) {
						tr[i].style.display = "";
					} else {
						tr[i].style.display = "none";
					}
				}
			}
		}

		(function ($) {
			$(window).on("load", function () {
				$(".content").mCustomScrollbar();
			});
		})(jQuery);
	</script>

	<!-- Favicons -->
	<link rel="apple-touch-icon" sizes="180x180" href="../img/favicons/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../img/favicons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../img/favicons/favicon-16x16.png">
	<link rel="manifest" href="../img/favicons/site.webmanifest">
	<link rel="mask-icon" href="../img/favicons/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#000000">
	<meta name="theme-color" content="#000000">

	<!-- Custom fonts for this template -->
	<script defer src="https://use.fontawesome.com/releases/v5.6.3/js/all.js"
			integrity="sha384-EIHISlAOj4zgYieurP0SdoiBYfGJKkgWedPHH4jCzpCXLmzVsw1ouK59MuUtP4a1"
			crossorigin="anonymous"></script>
	<link
		href="https://fonts.googleapis.com/css?family=Exo:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
		rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Press+Start+2P" rel="stylesheet">

	<!-- Custom styles for this template -->
	<link rel="stylesheet" href="../css/grayscale.css?v=1.0.0">
	<link rel="stylesheet" href="../css/itemsprites.min.css?v=1.0.0">
	<link rel="stylesheet" href="../css/npcsprites.min.css?v=1.0.0">
	<link rel="stylesheet" href="../css/jquery.mCustomScrollbar.min.css?v=1.0.0"/>

	<!-- Bootstrap style overrides -->
	<link rel="stylesheet" href="../css/style.min.css?v=1.0.1">

	<title>Open RSC</title>
</head>

<body id="page-top">
<nav>
	<label for="drop" class="toggle">
		<svg class="svg-inline--fa fa-bars fa-w-14" aria-hidden="true" data-prefix="fas" data-icon="bars" role="img"
			 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg="">
			<path fill="currentColor"
				  d="M16 132h416c8.837 0 16-7.163 16-16V76c0-8.837-7.163-16-16-16H16C7.163 60 0 67.163 0 76v40c0 8.837 7.163 16 16 16zm0 160h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16zm0 160h416c8.837 0 16-7.163 16-16v-40c0-8.837-7.163-16-16-16H16c-8.837 0-16 7.163-16 16v40c0 8.837 7.163 16 16 16z"></path>
		</svg>
		Navigation</label>

	<input type="checkbox" id="drop"/>
	<ul class="menu">
		<li><a href="/">Home</a></li>
		<li>
			<label for="drop-1" class="toggle">Download ▾</label>
			<a href="#">Download</a>
			<input type="checkbox" id="drop-1"/>
			<ul>
				<li><a href="https://game.openrsc.com/downloads/OpenRSC.jar">PC Client</a></li>
				<li><a href="https://game.openrsc.com/downloads/openrsc.apk">Android Client</a></li>
				<li><a href="https://github.com/open-rsc/single-player/releases" target="_blank">Single Player</a></li>
				<li><a href="https://github.com/open-rsc/game" target="_blank">Source Code</a></li>
			</ul>

		</li>
		<li><a href="/highscores/skill_total">Highscores</a></li>
		<li><a href="/chat">Recent Chat</a></li>
		<li><a href="/worldmap">Live Map</a></li>
		<li>
			<label for="drop-2" class="toggle">Information ▾</label>
			<a href="#">Information</a>
			<input type="checkbox" id="drop-2"/>
			<ul>
				<li><a href="/faq">FAQ</a></li>
				<li><a href="/rules">Rules</a></li>
				<li><a href="/shar">Shar's Bank</a></li>
				<li><a href="/calendar">Event Calendar</a></li>
				<li><a href="/stats">Game Statistics</a></li>
			</ul>
		</li>
		<li>
			<label for="drop-3" class="toggle">Guide ▾</label>
			<a href="#">Guide</a>
			<input type="checkbox" id="drop-3"/>
			<ul>
				<li><a href="/quest">Quest List</a></li>
				<li><a href="/minigames">Minigames</a></li>
				<li><a href="/wilderness">Wilderness Map</a></li>
				<li><a href="/items">Item Database</a></li>
				<li><a href="/npcs">NPC Database</a></li>
			</ul>
		</li>
		<li>
			<label for="drop-4" class="toggle">Reports ▾</label>
			<a href="#">Reports</a>
			<input type="checkbox" id="drop-4"/>
			<ul>
				<li><a href="https://goo.gl/forms/nnhSln7S81l4I26t2" target="_blank">Bug Reports</a></li>
				<li><a href="https://goo.gl/forms/AkBzpOzgAmzWiZ8H2" target="_blank">Bot Reports</a></li>
			</ul>
		</li>
		<li>
			<label for="drop-4" class="toggle">Links ▾</label>
			<a href="#">Links</a>
			<input type="checkbox" id="drop-4"/>
			<ul>
				<li><a href="https://discordapp.com/invite/94vVKND" target="_blank">
						<svg class="svg-inline--fa fa-discord fa-w-14 text-info mr-md-2" aria-hidden="true"
							 data-prefix="fab" data-icon="discord" role="img" xmlns="http://www.w3.org/2000/svg"
							 viewBox="0 0 448 512" data-fa-i2svg="">
							<path fill="currentColor"
								  d="M297.216 243.2c0 15.616-11.52 28.416-26.112 28.416-14.336 0-26.112-12.8-26.112-28.416s11.52-28.416 26.112-28.416c14.592 0 26.112 12.8 26.112 28.416zm-119.552-28.416c-14.592 0-26.112 12.8-26.112 28.416s11.776 28.416 26.112 28.416c14.592 0 26.112-12.8 26.112-28.416.256-15.616-11.52-28.416-26.112-28.416zM448 52.736V512c-64.494-56.994-43.868-38.128-118.784-107.776l13.568 47.36H52.48C23.552 451.584 0 428.032 0 398.848V52.736C0 23.552 23.552 0 52.48 0h343.04C424.448 0 448 23.552 448 52.736zm-72.96 242.688c0-82.432-36.864-149.248-36.864-149.248-36.864-27.648-71.936-26.88-71.936-26.88l-3.584 4.096c43.52 13.312 63.744 32.512 63.744 32.512-60.811-33.329-132.244-33.335-191.232-7.424-9.472 4.352-15.104 7.424-15.104 7.424s21.248-20.224 67.328-33.536l-2.56-3.072s-35.072-.768-71.936 26.88c0 0-36.864 66.816-36.864 149.248 0 0 21.504 37.12 78.08 38.912 0 0 9.472-11.52 17.152-21.248-32.512-9.728-44.8-30.208-44.8-30.208 3.766 2.636 9.976 6.053 10.496 6.4 43.21 24.198 104.588 32.126 159.744 8.96 8.96-3.328 18.944-8.192 29.44-15.104 0 0-12.8 20.992-46.336 30.464 7.68 9.728 16.896 20.736 16.896 20.736 56.576-1.792 78.336-38.912 78.336-38.912z"></path>
						</svg>
						Discord</a></li>
				<li><a href="https://github.com/open-rsc" target="_blank">
						<svg class="svg-inline--fa fa-github fa-w-16 text-info mr-md-2" aria-hidden="true"
							 data-prefix="fab" data-icon="github" role="img" xmlns="http://www.w3.org/2000/svg"
							 viewBox="0 0 496 512" data-fa-i2svg="">
							<path fill="currentColor"
								  d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8zM97.2 352.9c-1.3 1-1 3.3.7 5.2 1.6 1.6 3.9 2.3 5.2 1 1.3-1 1-3.3-.7-5.2-1.6-1.6-3.9-2.3-5.2-1zm-10.8-8.1c-.7 1.3.3 2.9 2.3 3.9 1.6 1 3.6.7 4.3-.7.7-1.3-.3-2.9-2.3-3.9-2-.6-3.6-.3-4.3.7zm32.4 35.6c-1.6 1.3-1 4.3 1.3 6.2 2.3 2.3 5.2 2.6 6.5 1 1.3-1.3.7-4.3-1.3-6.2-2.2-2.3-5.2-2.6-6.5-1zm-11.4-14.7c-1.6 1-1.6 3.6 0 5.9 1.6 2.3 4.3 3.3 5.6 2.3 1.6-1.3 1.6-3.9 0-6.2-1.4-2.3-4-3.3-5.6-2z"></path>
						</svg>
						GitHub</a></li>
				<li><a href="https://twitter.com/openrsc" target="_blank">
						<svg class="svg-inline--fa fa-twitter fa-w-16 text-info mr-md-2" aria-hidden="true"
							 data-prefix="fab" data-icon="twitter" role="img" xmlns="http://www.w3.org/2000/svg"
							 viewBox="0 0 512 512" data-fa-i2svg="">
							<path fill="currentColor"
								  d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"></path>
						</svg>
						Twitter</a></li>
				<li><a href="https://www.reddit.com/r/openrsc" target="_blank">
						<svg class="svg-inline--fa fa-reddit fa-w-16 text-info mr-md-2" aria-hidden="true"
							 data-prefix="fab" data-icon="reddit" role="img" xmlns="http://www.w3.org/2000/svg"
							 viewBox="0 0 512 512" data-fa-i2svg="">
							<path fill="currentColor"
								  d="M201.5 305.5c-13.8 0-24.9-11.1-24.9-24.6 0-13.8 11.1-24.9 24.9-24.9 13.6 0 24.6 11.1 24.6 24.9 0 13.6-11.1 24.6-24.6 24.6zM504 256c0 137-111 248-248 248S8 393 8 256 119 8 256 8s248 111 248 248zm-132.3-41.2c-9.4 0-17.7 3.9-23.8 10-22.4-15.5-52.6-25.5-86.1-26.6l17.4-78.3 55.4 12.5c0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.3 24.9-24.9s-11.1-24.9-24.9-24.9c-9.7 0-18 5.8-22.1 13.8l-61.2-13.6c-3-.8-6.1 1.4-6.9 4.4l-19.1 86.4c-33.2 1.4-63.1 11.3-85.5 26.8-6.1-6.4-14.7-10.2-24.1-10.2-34.9 0-46.3 46.9-14.4 62.8-1.1 5-1.7 10.2-1.7 15.5 0 52.6 59.2 95.2 132 95.2 73.1 0 132.3-42.6 132.3-95.2 0-5.3-.6-10.8-1.9-15.8 31.3-16 19.8-62.5-14.9-62.5zM302.8 331c-18.2 18.2-76.1 17.9-93.6 0-2.2-2.2-6.1-2.2-8.3 0-2.5 2.5-2.5 6.4 0 8.6 22.8 22.8 87.3 22.8 110.2 0 2.5-2.2 2.5-6.1 0-8.6-2.2-2.2-6.1-2.2-8.3 0zm7.7-75c-13.6 0-24.6 11.1-24.6 24.9 0 13.6 11.1 24.6 24.6 24.6 13.8 0 24.9-11.1 24.9-24.6 0-13.8-11-24.9-24.9-24.9z"></path>
						</svg>
						Reddit</a></li>
			</ul>
		</li>
	</ul>
</nav>
