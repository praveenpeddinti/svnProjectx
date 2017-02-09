import { Component, ElementRef, NgZone, Input,Output, ViewChild, EventEmitter } from '@angular/core';

import { MentionDirective } from 'angular2-mentions/mention/mention.directive';
import { COMMON_NAMES } from './common-names';

declare var tinymce: any;


/**
 * Angular 2 Mentions.
 * https://github.com/dmacfarlane/angular2-mentions
 *
 * Example usage with TinyMCE.
 */
@Component({
  selector: 'tinymce',
  template: `
    <div class="form-group" style="position:relative">{{data}}
      <div [mention]="items"></div>
      <div>
        <textarea [(ngModel)]="data" class="hidden" cols="60" rows="4" id="tmce">{{htmlContent}}</textarea>        
      </div>     
    </div>
 `,

    outputs:['eventEmitter']
})
export class TinyMCE {
  public _myModel = '';
  

  @Input() data:string;
  @Output()  

 
  // set name(myModel: string) {
  //   this._myModel = (myModel && myModel.trim()) || '<no myModel set>';
 // }
  @Input() htmlContent;
  @ViewChild(MentionDirective) mention: MentionDirective;
  protected items:string[]= COMMON_NAMES;
  constructor(private _elementRef: ElementRef, private _zone: NgZone) {}

  eventEmitter = new EventEmitter<string>();
  onChange(value:string){
    console.log("the data :" + value);
    this.eventEmitter.emit(value);
  }

  ngAfterViewInit() {
    tinymce.init({
      mode: 'exact',
      height: 100,
      theme: 'modern',
      plugins: [
        'lists link ',
        
        
      ],
      toolbar: ' bold italic underline | link | bullist numlist ',
      elements: "tmce",
      setup: this.tinySetup.bind(this)
    }
    );
  }
  tinySetup(ed) {
    let comp = this;
    let mention = this.mention;
    ed.on('keydown', function(e) {
      let frame = <any>window.frames[ed.iframeElement.id];
      let contentEditable = frame.contentDocument.getElementById('tinymce');
      comp._zone.run(() => {
        comp.mention.keyHandler(e, contentEditable);
      });
    });
    ed.on('init', function(args) {
      mention.setIframe(ed.iframeElement);
    });
  }
}
