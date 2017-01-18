import * as io from 'socket.io-client';
import { Component } from '@angular/core';


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
  constructor()
  {

  }

  public sendMessage(message:any)
  {
    this.socket = io(this.url);
    // this.socket.emit('projectSearch', message); 
     this.socket.emit('add-message', message); 
  }
}
