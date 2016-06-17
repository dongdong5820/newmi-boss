/**
 * 
 */
 function opts(selectObj){
		 var optDivs=document.createElement("div"); 
		 var objTable=document.createElement("table"); 
		 var objTbody=document.createElement("tbody"); 
		 optDivs.style.zIndex = "100"; 
		 objTable.style.zIndex = "100"; 
		 objTable.width=selectObj.style.width;     
		 objTable.border = "0"; 
		 objTable.cellPadding = "0"; 
		 objTable.cellSpacing = "0"; 
		 objTable.style.paddingLeft = "2";     
		 objTable.style.fontFamily = "Verdana, Arial, Helvetica, sans-serif"; 
		
		 var e = selectObj; 
		 var absTop = e.offsetTop; 
		 var absLeft = e.offsetLeft; 
		 var absWidth = e.offsetWidth; 
		 var absHeight = e.offsetHeight; 
		
	 while(e = e.offsetParent){ 
		 absTop += (e.offsetTop+0.3); 
		 absLeft += e.offsetLeft; 
	 } 
	
	 with (objTable.style){ 
		 position = "absolute"; 
		 top = (absTop + absHeight) + "px"; 
		 left = (absLeft+1) + "px"; 
		 border = "1px solid black"; 
		 tableLayout="fixed"; 
		 wordBreak="break-all"; 
	 } 
	
	 var options = selectObj.options; 
	 var val=selectObj.value; 
	
	if (options.length > 0){ 
	     for (var i = 0; i < options.length; i++){ 
	         var newOptDiv = document.createElement("td"); 
			 var objRow=document.createElement("tr"); 
			 newOptDiv.name=options[i].value; 
			 newOptDiv.innerText=options[i].innerText; 
			 newOptDiv.title=options[i].title; 
			 newOptDiv.onmouseout = function() {this.className='smouseOut';val=selectObj.value}; 
			 newOptDiv.onmouseover = function() {this.className='smouseOver';val=this.name;}; 
			 newOptDiv.className="smouseOut"; 
			 newOptDiv.style.width=40; 
			 newOptDiv.style.cursor="default"; 
			 newOptDiv.style.fontSize = "11px"; 
			 newOptDiv.style.fontFamily = "Verdana, Arial, Helvetica, sans-serif"; 
			
			 objRow.appendChild(newOptDiv); 
			 objTbody.appendChild(objRow); 
		 } 
	} 
	
	 
    objTbody.appendChild(objRow); 
    objTable.appendChild(objTbody); 
    optDivs.appendChild(objTable); 
    document.body.appendChild(optDivs); 
	
    var IfrRef = document.createElement("div"); 
	IfrRef.style.position="absolute"; 
    IfrRef.style.width = objTable.offsetWidth; 
    IfrRef.style.height = objTable.offsetHeight; 
    IfrRef.style.top = objTable.style.top; 
    IfrRef.style.left = objTable.style.left; 
    IfrRef.style.backgroundColor = document.bgColor; 
    document.body.appendChild(IfrRef); 

    objTable.focus(); 
    objTable.onblur=function() {choose(selectObj,val,optDivs,IfrRef)}; 
 } 

 function choose(objselect,val,delobj,delobj2){ 
     objselect.value=val; 
     document.body.removeChild(delobj); 
     document.body.removeChild(delobj2); 
 } 