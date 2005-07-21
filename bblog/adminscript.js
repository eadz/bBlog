function ulcheck()
    	{
    		document.getElementById('fileul').style.display='block';
    		document.getElementById('fileserv').style.display='none';
    	}
    	
function servcheck()
    	{
    		document.getElementById('fileul').style.display='none';
    		document.getElementById('fileserv').style.display='block';
    	}
    	
function nonecheck()
    	{
    		document.getElementById('fileul').style.display='none';
    		document.getElementById('fileserv').style.display='none';
    	}
function refresh()
{
    // odmazeme stary skript, pokud existoval
    var hlavicka = document.getElementsByTagName('head')[0];
    var dataLoader = document.getElementById('scriptLoader');
    if(dataLoader) hlavicka.removeChild(dataLoader);

    // vytvorime novy element script
    script = document.createElement('script');
    script.id = 'scriptLoader';
    script.src = './script/reload.php?reload='+Math.random();

    // vlozime do stranky, cimz prohlizec stahne skript
    x = document.getElementsByTagName('head')[0];
    x.appendChild(script);
		timeout();
    return false;
}

function timeout()
{
	setTimeout( "refresh()", 5*60000 ); // get session id every 5 minutes
}

// onload
timeout();