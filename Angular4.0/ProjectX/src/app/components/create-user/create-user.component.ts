import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-create-user',
  templateUrl: './create-user.component.html',
  styleUrls: ['./create-user.component.css']
})
export class CreateUserComponent implements OnInit {
  public form={};
  public isEmailValid;
  public isPasswordMatch:boolean=false;
  constructor() { }

  ngOnInit() {
  }

  validateEmail(email){
      var pattern=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      this.isEmailValid = pattern.test(email); 
    }

    matchPassword()
    {
      if(this.form['password'] ==this.form['confirmpassword'])
      {
        this.isPasswordMatch=true;
      }

      if(this.isPasswordMatch)
      {
        // Make an ajax to save the User
      }
      else
      {
        //Error Message for Mismatch Password
      }
    }

}
