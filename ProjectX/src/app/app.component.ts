import * as io from 'socket.io-client';
import { Component } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { Headers, Http } from '@angular/http';
declare var jQuery:any;

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  title = 'app works!';
  private socket;
  private url='http://localhost:5000';
 // private url='http://10.10.73.22:7007';
     private route: ActivatedRoute;

  constructor(
    public _router: Router,
    private http: Http,
    
  ){}
   ngOnInit() {

  }
  /*
  * Added by Padmaja 
  *After the view is initialized,this script will be available
  */     
   ngAfterViewInit() {
      var thiscall=this;
      jQuery(document).ready(function(){
        thiscall.setFooterHeight();
      jQuery( window ).resize(function() {
        thiscall.setFooterHeight();
      }); 
     });
  }
  public sendMessage(message:any)
  {
    this.socket = io(this.url);
    // this.socket.emit('projectSearch', message); 
     this.socket.emit('add-message', message); 
  }
  public setFooterHeight()
  {
     var windowHeight=jQuery( window ).height();
        setTimeout(()=>{
          var headerHeight=jQuery('.navbar-inverse').height()
          var footerHeight=jQuery('.loginfooter').height();
          var AddedValue=headerHeight+footerHeight+50;
          var finalVal=windowHeight-AddedValue;
     
         jQuery("#setHeight").css('min-height',finalVal);
      },250);
  }
}
