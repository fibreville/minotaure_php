  </div>
  <script>
	document.body.className=document.body.className.replace('light',localStorage.getItem('bright')||'light');
	function setBright(holdBright,newBright){
		localStorage.setItem('bright',newBright);
		document.body.className=document.body.className.replace(holdBright,newBright);
	}</script>
</body>