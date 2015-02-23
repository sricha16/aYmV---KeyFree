<!-- style.php 
     provides the style for the website in CSS
     describes the navigation bar for the website -->

<!DOCTYPE html>
<html>
<head>
	<style>
		body{
			background: url(created4.png);
			font: 13px 'trebuchet MS', Arial, Helvetica;
		}
		
		.text{
			/*BACKGROUND*/
			background-image: url(created.png);
			
			/*BORDER*/
			border-width: 5px;
			border-style: solid;
			border-color: #F2C409;
			/*adds shadow*/
			box-shadow: 0px 5px 5px #444444;
			-moz-box-shadow: 0px 5px 5px #444444;
			-webkit-box-shadow: 0px 5px 5px #444444;
			/*adds rounded edges*/
			-moz-border-radius: 10px;
			-webkit-border-radius: 10px;
			border-radius: 10px; /* future proofing */
			-khtml-border-radius: 10px; /* for old Konqueror browsers */
			
			/*FORMATTING*/
			/*center*/
			width:960px;
  			margin:auto;
  			text-align: center;
  			/*other*/
  			padding: 10px;
  			
  			/*TEXT*/
  			color: #F2C409;
  			font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;  // WAs CURSIVE LOLS 
		}
		
		.message{
		
			/*TEXT*/
			font-weight: bold; 
			text-align: center;
			color: #F2C409;
			
			/*BACKGROUND*/
			/*color*/
			background-color: #4a0087;
			/*shadow*/
			box-shadow: 0px 5px 5px #444444;
			-moz-box-shadow: 0px 5px 5px #444444;
			-webkit-box-shadow: 0px 5px 5px #444444;
			/*rounded edges*/
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			border-radius: 5px; /* future proofing */
			-khtml-border-radius: 5px; /* for old Konqueror browsers */
		}
		
		.input-box{
		
			/*BACKGROUND*/
			
		
			/*BORDER*/
			border-width: 2px;
			border-style: solid;
			border-color: #4E0087;
			/*adds shadow*/
			box-shadow: 0px 5px 5px #444444;
			-moz-box-shadow: 0px 5px 5px #444444;
			-webkit-box-shadow: 0px 5px 5px #444444;
			/*adds rounded edges*/
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			border-radius: 5px; /* future proofing */
			-khtml-border-radius: 5px; /* for old Konqueror browsers */
			
			/*FORMATTING*/
			/*centers*/
			display: block;
			width: 150px;
			margin: auto;
			text-align: center;
		}
		
		.button-style{
			/*TEXT*/
			font-weight: bold; 
			color: #F2C409;
			
			/*BACKGROUND*/
			/*color*/
			background-color: #4E0087;
			/*shadow*/
			box-shadow: 0px 5px 5px #444444;
			-moz-box-shadow: 0px 5px 5px #444444;
			-webkit-box-shadow: 0px 5px 5px #444444;
			
			/*BORDER*/
			border-width: 2px;
			border-style: solid;
			border-color: #444444;
			/*rounded edges*/
			-moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			border-radius: 5px; /* future proofing */
			-khtml-border-radius: 5px; /* for old Konqueror browsers */
		
			/*FORMATTING*/
			/*center*/
			display: block;
			margin: auto;
		}
		
		
		#menu, #menu ul {
			margin: 0;
			padding: 0;
			list-style: none;
		}
		/*THE STUFF I AM LOOING T CHANGE IS HERE  DISREGARD THSI COMMENT*/
		#menu {
			width: 560px;
			margin: 0px auto;
			border: 1px solid #222;
			/*background-color: #4E0087;*/
			background-image: url(created.png);
			border-radius: 6px;
			border-width:2px;
			border-style: solid;
			border-color: #F2C409;
			box-shadow: 0 1px 1px #777, 0 1px 0 #666 inset;
			float: center;
		}
		
		#menu:before,
		#menu:after {
			content: "";
			display: table;
		}
		
		#menu:after {
			clear: both;
		}
		
		#menu {
			zoom:1;
		}
		
		#menu li {
			float: left;
			border-right: 0px solid #222;
			box-shadow: 0px 0 0 #444;     // EDIT THIS LINE FOR THE LINES BETWEEN MENU
			position: relative;
		}
		
		#menu a {
			float: left;
			padding: 12px 30px;
			color: #F2C409;
			text-transform: uppercase;
			font: bold 12px Arial, Helvetica;
			text-decoration: none;
			text-shadow: 0 1px 0 #000;
		}
		
		
		
		
		#menu ul {
			margin: 20px 0 0 0;
			_margin: 0; /*IE6 only*/
			opacity: 0;
			visibility: hidden;
			position: absolute;
			top: 38px;
			left: 0;
			z-index: 1;    
			background: #444;
			background: linear-gradient(#444, #4E0087);
			box-shadow: 0 -1px 0 rgba(255,255,255,.3);	
			border-radius: 3px;
			transition: all .2s ease-in-out;  
		}
		#menu ul ul {
			top: 0;
			left: 150px;
			margin: 0 0 0 20px;
			_margin: 0; /*IE6 only*/
			box-shadow: -1px 0 0 rgba(255,255,255,.3);		
		}
		
		#menu ul li {
			float: none;
			display: block;
			border: 0;
			_line-height: 0; /*IE6 only*/
			box-shadow: 0 1px 0 #111, 0 2px 0 #666;
		}
		
		#menu ul li:last-child {   
			-moz-box-shadow: none;
			-webkit-box-shadow: none;
			box-shadow: none;    
		}
		
		#menu ul a {    
			padding: 10px;
			width: 130px;
			_height: 10px; /*IE6 only*/
			display: block;
			white-space: nowrap;
			float: none;
			text-transform: none;
		}
		
		#menu ul a:hover {
			//can add these in if we want, looks weird right now because can't change the letter color too
			background-color: #444444;
			background-image: linear-gradient(#AAAAAA, #BBBBBB);
		}
		
		/*can take this and some above here out if we don't have sub menues*/
		#menu ul li:first-child > a {
			border-radius: 3px 3px 0 0;
		}
		
		#menu ul li:first-child > a:after {
			content: '';
			position: absolute;
			left: 40px;
			top: -6px;
			border-left: 6px solid transparent;
			border-right: 6px solid transparent;
			border-bottom: 6px solid #444;
		}
		
		#menu ul ul li:first-child a:after {
			left: -6px;
			top: 50%;
			margin-top: -6px;
			border-left: 0;	
			border-bottom: 6px solid transparent;
			border-top: 6px solid transparent;
			border-right: 6px solid #3b3b3b;
		}
		
		#menu ul li:first-child a:hover:after {
			border-bottom-color: #444; 
		}
		
		#menu ul ul li:first-child a:hover:after {
			border-right-color: #444; 
			border-bottom-color: #444; 	
		}
		
		#menu ul li:last-child > a {
			-moz-border-radius: 0 0 3px 3px;
			-webkit-border-radius: 0 0 3px 3px;
			border-radius: 0 0 3px 3px;
		}
		
		/* Mobile */
		#menu-trigger {
			display: none;
		}
	
		@media screen and (max-width: 600px) {
			
			
			
			/* nav-wrap */
			#menu-wrap {
				position: relative;
			}
	
			#menu-wrap * {
				-moz-box-sizing: border-box;
				-webkit-box-sizing: border-box;
				box-sizing: border-box;
			}
	
			/* menu icon */
			#menu-trigger {
				display: block; /* show menu icon */
				height: 40px;
				line-height: 40px;
				width: 150px;
				cursor: pointer;		
				padding: 0 0 0 35px;
				border: 1px solid #222;
				color: #fafafa;
				font-weight: bold;
				/*background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAPCAMAAADeWG8gAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjE2QjAxNjRDOUNEOTExRTE4RTNFRkI1RDQ2MUYxOTQ3IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjE2QjAxNjREOUNEOTExRTE4RTNFRkI1RDQ2MUYxOTQ3Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6MTZCMDE2NEE5Q0Q5MTFFMThFM0VGQjVENDYxRjE5NDciIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6MTZCMDE2NEI5Q0Q5MTFFMThFM0VGQjVENDYxRjE5NDciLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz42AEtnAAAABlBMVEX///////9VfPVsAAAAAnRSTlP/AOW3MEoAAAAWSURBVHjaYmAgFzBiACKFho6NAAEGAD07AG1pn932AAAAAElFTkSuQmCC) no-repeat 10px center, linear-gradient(#444, #4E0087);*/
				border-radius: 6px;
				box-shadow: 0 1px 1px #777, 0 1px 0 #666 inset;
			}
			
			/* main nav */
			#menu {
				margin: 0; padding: 10px;
				position: static;
				top: 40px;
				width: 150px;
				z-index: 1;
				background-color: #444;
				display: none;
				box-shadow: none;		
			}
	
			#menu:after {
				content: '';
				position: absolute;
				left: 25px;
				top: -8px;
				border-left: 8px solid transparent;
				border-right: 8px solid transparent;
				border-bottom: 8px solid #444;
			}	
	
			#menu ul {
				position: static;
				visibility: visible;
				opacity: 1;
				margin: 0;
				background: none;
				box-shadow: none;				
			}
	
			#menu ul ul {
				margin: 0 0 0 20px !important;
				box-shadow: none;		
			}
	
			#menu li {
				position: static;
				display: block;
				float: none;
				border: 0;
				margin: 5px;
				box-shadow: none;			
			}
	
			#menu ul li{
				margin-left: 20px;
				box-shadow: none;		
			}
	
			#menu a{
				display: block;
				float: none;
				padding: 0;
				color: #999;
			}
	
			#menu a:hover{
				color: #F2C409;
			}	
	
			#menu ul a{
				padding: 0;
				width: auto;		
			}
	
			#menu ul a:hover{
				background: none;	
			}

		#menu ul li:first-child a:after,
		#menu ul ul li:first-child a:after {
			border: 0;
		}		

	}

	@media screen and (min-width: 600px) {
		#menu {
			display: block !important;
		}
	}	

	/* iPad */
	.no-transition {
		transition: none;
		opacity: 1;
		visibility: visible;
		display: none;  		
	}

	#menu li:hover > .no-transition {
		display: block;
	}
	</style>
	
	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
	
	<script>
		// CSS3 animated & responsive dropdown menu
		// http://www.red-team-design.com/css3-animated-dropdown-menu
		$(function(){
				/* Mobile */
				$('#menu-wrap').prepend('<div id="menu-trigger">Menu</div>');		
				$("#menu-trigger").on("click", function(){
					$("#menu").slideToggle();
				});
		
				// iPad
				var isiPad = navigator.userAgent.match(/iPad/i) != null;
				if (isiPad) $('#menu ul').addClass('no-transition');      
		});
	</script>
	
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<title>key-free!</title>
	<link rel="shortcut icon" href="icon.png">
	
</head>

<body>
	<center>
		<img src="logoColorized.png" height=200px align="middle">
	</center>
	<nav id="menu-wrap">    
	<ul id="menu">
		
		<li>
			<a href="index.php" >Home</a>
			<ul>
				<li><a href="">CSS</a></li>
			</ul>
		</li>
		<li><a href="retrieval.php" >Retrieval</a></li>
		<li><a href="entry.php" >Entry</a></li>
                <li><a href="aboutus.php" >About Us</a></li>
                <li><a href="help.php">Help</a></li>
	</ul>
	</nav><br><br>
	
		
</body>

</html>