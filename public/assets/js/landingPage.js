function comprovaInput(update) {
    var usernameRegex = /^[A-Za-z0-9_-]+$/i;
    var emailRegex = /^\S+@\S+\.\S+$/;
    var passwordRegex = /^(?=(?:.*\d){1})(?=(?:.*[A-Z]){1})\S+$/;
    var textError = '';
    var username = document.getElementById('username').value;
    var email = document.getElementById('email').value;
    var birthday = document.getElementById('birthday').value;
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirmPassword').value;

    //comprovar si el username existe ya en la app

    if(update != 'update'){
        if(usernameRegex.test(username)){
            document.getElementById('error1').textContent = '';
            document.getElementById('username').style.borderColor = "green";

        }else{
            textError = document.createTextNode("*El nombre debe contener solo caracteres alfanumericos.");
            document.getElementById('error1').textContent = '';
            document.getElementById('error1').appendChild(textError);
        }

        if(username.length > 20){
            textError = document.createTextNode("*El usuario no puede contener más de 20 carácteres.");
            document.getElementById('error1').textContent = '';
            document.getElementById('error1').appendChild(textError);
        }

        if(username.length == 0){
            document.getElementById('error1').textContent = '';
        }
    }


    if(emailRegex.test(email)){
        document.getElementById('error2').textContent = '';
        document.getElementById('email').style.borderColor = "green";
    }else{
        textError = document.createTextNode("*Introduzca un mail válido.");
        document.getElementById('error2').textContent = '';
        document.getElementById('error2').appendChild(textError);
    }

    if(email.length == 0){
        document.getElementById('error2').textContent = '';
    }

    if(update != 'update'){

        birthday.toString();
        var year = birthday.charAt(0).concat(birthday.charAt(1));
        year = year.concat(birthday.charAt(2));
        year = year.concat(birthday.charAt(3));
        year = parseInt(year);

        if(year < 1850 || year > 2018){
            textError = document.createTextNode("*Introduzca un año de nacimiento válido.");
            document.getElementById('error3').textContent = '';
            document.getElementById('error3').appendChild(textError);
        }else{
            document.getElementById('error3').textContent = '';
        }
    }

    if(password.length < 6 || password.length > 12){
        textError = document.createTextNode("*Introduzca una contraseña que contenga entre 6 y 12 caractéres.");
        document.getElementById('error4').textContent = '';
        document.getElementById('error4').appendChild(textError);
    }else if(!passwordRegex.test(password)){
        textError = document.createTextNode("*Introduzca una contraseña que contenga al menos un número y una mayúscula.");
        document.getElementById('error4').textContent = '';
        document.getElementById('error4').appendChild(textError);
    }else{
        document.getElementById('error4').textContent = '';
        document.getElementById('password').style.borderColor = "green";
    }

    if(password.length == 0){
        document.getElementById('error4').textContent = '';
    }

    if(confirmPassword != password){
        textError = document.createTextNode("*Las contraseñas no coinciden.");
        document.getElementById('error5').textContent = '';
        document.getElementById('error5').appendChild(textError);
    }else if(password != 0){
        document.getElementById('error5').textContent = '';
        document.getElementById('confirmPassword').style.borderColor = "green";
    }

    if(confirmPassword.length == 0){
        document.getElementById('error5').textContent = '';
    }

    if(update == 'login'){
        email = document.getElementById('emailLogin').value;
        password = document.getElementById('passwordLogin').value;


        if(emailRegex.test(email)){
            document.getElementById('error6').textContent = '';
            document.getElementById('emailLogin').style.borderColor = "green";
        }else{
            textError = document.createTextNode("*Introduzca un mail válido.");
            document.getElementById('error6').textContent = '';
            document.getElementById('error6').appendChild(textError);
        }

        if(email.length == 0){
            document.getElementById('error6').textContent = '';
        }

        if(password.length < 6 || password.length > 12){
            textError = document.createTextNode("*Introduzca una contraseña que contenga entre 6 y 12 caractéres.");
            document.getElementById('error7').textContent = '';
            document.getElementById('error7').appendChild(textError);
        }else if(!passwordRegex.test(password)){
            textError = document.createTextNode("*Introduzca una contraseña que contenga al menos un número y una letra mayúscula.");
            document.getElementById('error7').textContent = '';
            document.getElementById('error7').appendChild(textError);
        }else{
            document.getElementById('error7').textContent = '';
            document.getElementById('passwordLogin').style.borderColor = "green";
        }

        if(password.length == 0){
            document.getElementById('error7').textContent = '';
        }
    }
}


function sendForm(form, delete_user) {
    var id = document.getElementById(form.id).id;
        document.getElementById(id).submit();
}
