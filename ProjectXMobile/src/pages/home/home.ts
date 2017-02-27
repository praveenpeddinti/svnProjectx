import { Component } from '@angular/core';
import {NavController, ViewController, AlertController, LoadingController } from 'ionic-angular';

//For pages
import {WelcomePage} from '../welcome/welcome';

//For Services
import {Globalservice} from '../../providers/globalservice';

//For constants
import {Constants} from '../../providers/constants';
//For local data storage 
import {Storage} from "@ionic/storage";

@Component({
  selector: 'page-home',
  templateUrl: 'home.html',
  providers: [Globalservice, Constants]
})
export class HomePage {
    
    login: {username?: string, password?: string,token?:any} = {};

    constructor(public navCtrl: NavController, 
        private loginService: Globalservice,
        private storage:Storage,
        public alertController: AlertController,
        public loadingController: LoadingController,
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
           let loader = this.loadingController.create({ content: "Loading..."});  
          loader.present();
      
          this.loginService.getLoginValidation(this.urlConstants.loginUrl, this.login).subscribe(
          
                        data=>{
                            if (data.status=='200'){
                            
                            console.log("the response " + JSON.stringify(data));
                             this.storage.set('username',this.login.username);
                             this.storage.set('password',this.login.password); 
                             this.storage.set('token',this.login.token);

                            let userCredentials = {username: this.login.username};
                            this.navCtrl.push(WelcomePage, userCredentials);
                           // loader.dismiss();
                            loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                            this.viewCtrl.dismiss();
                      } else{
                        loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                           let alert = this.alertController.create({
                           title: 'Warning',
                           message: 'Invalid username and password.',
                            buttons: [
                            {
                             text: 'Ok',
                             role: 'cancel',
                             handler: () => {
                               this.navCtrl.push(HomePage);
                             console.log('Cancel clicked');
                             this.viewCtrl.dismiss();
                              }
                            }
                           ]
                         });
                        alert.present();
                      }
                        },
                        error=>{
                            console.log("the error " + JSON.stringify(error));
                        },
                        ()=> console.log('login api call complete')
                        );
          

                        
      }
  }

}
