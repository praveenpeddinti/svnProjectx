import { Component } from '@angular/core';
import {NavController, ViewController, AlertController, LoadingController } from 'ionic-angular';

//For pages
import {DashboardPage} from '../dashboard/dashboard';

//For Services
import {Globalservice} from '../../providers/globalservice';

//For constants
import {Constants} from '../../providers/constants';
//For local data storage 
import { Storage } from "@ionic/storage";

/*
  Generated class for the Login page.

*/
@Component({
  selector: 'page-login',
  templateUrl: 'login.html',
  providers: [Globalservice, Constants]
})
export class LoginPage {

    login: {username?: string, password?: string,token?:any} = {};
    public submitted = false;
    public isEmailValid=true;
    
    constructor(public navCtrl: NavController, 
                private loginService: Globalservice,
                private storage: Storage,
                public alertController: AlertController,
                public loadingController: LoadingController,
                private urlConstants: Constants,
                public viewCtrl: ViewController) {
     
  }
  
   onLogin(form): void{
     
      if (form.valid) {
          console.log("the values " + this.login.username + " ----- " + this.urlConstants.loginUrl );
           let loader = this.loadingController.create({ content: "Loading..."});  
          loader.present();
      
          this.loginService.getLoginValidation(this.urlConstants.loginUrl, this.login).subscribe(
          
                result=>{
                        if (result.status=='200'){

                            this.storage.ready().then( ()=>{
                                this.storage.set('userCredentials', result.data).then( ()=>{

                                    let userCredentials = {username: this.login.username};
                                    //this.navCtrl.push(WelcomePage, userCredentials);
                                    this.navCtrl.push(DashboardPage, userCredentials);
                                   // loader.dismiss();
                                    loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                                    this.viewCtrl.dismiss();

                                },
                                (error)=>{
                                    console.log("error while storing");
                                });
                            });

                      } else{
                        loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                           let alert = this.alertController.create({
                           title: 'Warning',
                           message: 'Invalid username or password.',
                            buttons: [
                            {
                             text: 'Ok',
                             role: 'cancel'
                            }
                           ]
                         });
                        alert.present();
                      }
                },
                error=>{
                  loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                    console.log("the error " + JSON.stringify(error));
                },
                ()=> console.log('login api call complete')
            );
              
      }
  }


}
