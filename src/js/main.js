function setTheme(theme_name){
    localStorage.setItem('theme_name',theme_name);
    document.documentElement.setAttribute('data-theme', theme_name);
}

if (localStorage.getItem('theme_name')) {
    setTheme(localStorage.getItem('theme_name'));
}