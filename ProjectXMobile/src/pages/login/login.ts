import { Component } from '@angular/core';
import {NavController, ViewController,  AlertController, LoadingController,  } from 'ionic-angular';
import {DashboardPage} from '../dashboard/dashboard';
import {Globalservice} from '../../providers/globalservice';
import {Constants} from '../../providers/constants';
import { Storage } from '@ionic/storage';
/*
  Generated class for the Login page.
*/
@Component({
  selector: 'page-login',
  templateUrl: 'login.html',
  providers: [Globalservice, Constants]
})
export class LoginPage {
    login: {username?: string, password?: string} = {};
    public submitted = false;
    public isEmailValid=true;
    public hideElement: boolean=true;
    constructor(public navCtrl: NavController, 
                private loginService: Globalservice,
                private storage: Storage,
                public alertController: AlertController,
                public loadingController: LoadingController,
                private urlConstants: Constants,
                public viewCtrl: ViewController) {}
   public onLogin(form): void{
      if (form.valid) {
          let loader = this.loadingController.create({ content: "Loading..."});  
          loader.present();
          this.loginService.getLoginValidation(this.urlConstants.loginUrl, this.login).subscribe(
                (result)=>{
                        if (result.status=='200'){
                            localStorage.setItem("userCredentials",JSON.stringify(result.data));
                           // alert(localStorage.getItem("userCredentials"));
                            // alert('out-'+ this.login.username);
                             let userCredentials = {username: this.login.username};
                             this.navCtrl.setRoot(DashboardPage, userCredentials);
                             loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                      } else{
                        loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                        this.hideElement=false;
                      }
                },
                (error)=>{
                    loader.dismiss().catch(() => console.log('ERROR CATCH: LoadingController dismiss'));
                    console.log("the error " + JSON.stringify(error));
                },
                ()=> {console.log('login api call complete')
            });
      }
  }
}