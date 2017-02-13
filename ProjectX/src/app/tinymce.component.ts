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
        <textarea class="hidden" cols="60" rows="4" id="tmce">{{htmlContent}}</textarea>        
      </div>     
    </div>
 `
})
export class TinyMCE {
  //The internal data model
 /**
  * @author:TinyMCE (modified by Ryan@Techo2)
  * Purpose:Custom Model Binding using @Input and @Output
  */
    innerValue = '';
    @Output() contentChange:EventEmitter<string> = new EventEmitter<string>();

    //get accessor
    get content() {
        return this.innerValue;
    };
   
    //set accessor emitting changes to the model 
    @Input()
    set content(v) {
        // if (v !== this.innerValue) {
        //     this.innerValue = v;
        //     this.onChangeCallback(v);
        // }
        this.innerValue=v;
        this.contentChange.emit(this.innerValue);
    }
  @Input() htmlContent;
  @ViewChild(MentionDirective) mention: MentionDirective;
  protected items:string[]= COMMON_NAMES;
  constructor(private _elementRef: ElementRef, private _zone: NgZone) {}
  ngAfterViewInit() {
    tinymce.init({
      mode: 'exact',
      height: 100,
      theme: 'modern',
menubar:false,
    statusbar: false,
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
    let content=this.contentChange;
    ed.on('keydown', function(e) {
      let frame = <any>window.frames[ed.iframeElement.id];
      let contentEditable = frame.contentDocument.getElementById('tinymce');
      comp._zone.run(() => {
        comp.mention.keyHandler(e, contentEditable);
        this.innerValue=tinymce.activeEditor.getContent();
        content.emit(this.innerValue);
      });
    });
    ed.on('init', function(args) {
      mention.setIframe(ed.iframeElement);
    });
  }
}
