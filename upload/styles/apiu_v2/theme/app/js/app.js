	/**
	 * Function that open and close element
 	 */
	function collapse(el){
	
		var classList =	el.classList;

		if (classList.contains('visible')){
			classList.remove('visible');
			classList.add('invisible');
		}
		else{
			classList.add('visible');
			classList.remove('invisible');
		}
	}
