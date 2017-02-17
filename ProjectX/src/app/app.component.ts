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
     var getAllObj=JSON.parse(localStorage.getItem("user"));
    if(getAllObj != null){
    // this._router.navigate(['story-dashboard']); 
   
    }else{
       this._router.navigate(['login']); 
    }
    //alert(jQuery('.container-fluid').height());
  }
  public sendMessage(message:any)
  {
    this.socket = io(this.url);
    // this.socket.emit('projectSearch', message); 
     this.socket.emit('add-message', message); 
  }
}
