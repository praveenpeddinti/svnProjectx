import { Component } from '@angular/core';
import {NavController, ViewController } from 'ionic-angular';

//For pages
import {WelcomePage} from '../welcome/welcome';

//For Services
import {Globalservice} from '../../providers/globalservice';

//For constants
import {Constants} from '../../providers/constants'

@Component({
  selector: 'page-home',
  templateUrl: 'home.html',
  providers: [Globalservice, Constants]
})
export class HomePage {
    
    login: {username?: string, password?: string} = {};

    constructor(public navCtrl: NavController, 
        private loginService: Globalservice,
        private urlConstants: Constants,
        public viewCtrl: ViewController) {
    
  }
  
   onLogin(form): void{
//   let user_data = {
//    'username': this.login.username,
//    'password': this.login.password,
//    'AccessKey': '3fd31d9a7ae286b9c6da983b35359915'
//  }
      if (form.valid) {
          console.log("the values " + this.login.username + " ----- " + this.urlConstants.loginUrl );
          
          this.loginService.getLoginValidation(this.urlConstants.loginUrl, this.login).subscribe(
                        data=>{
                            console.log("the response " + JSON.stringify(data));
                        }, 
                        error=>{
                            console.log("the error " + JSON.stringify(error));
                        },
                        ()=> console.log('login api call complete')
                        );
          
          let data = {username: this.login.username};
          this.navCtrl.push(WelcomePage, data);
          
          this.viewCtrl.dismiss();
      }
  }

}
