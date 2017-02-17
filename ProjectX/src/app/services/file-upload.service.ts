import { Injectable } from '@angular/core';

@Injectable()
export class FileUploadService {

  public xhrFileUploadStatus:boolean = false;

  constructor() { }

public makeFileRequest(url: string, params: Array<string>, files: Array<File>) {
        return new Promise((resolve, reject) => {
            var formData: any = new FormData();
            var xhr = new XMLHttpRequest();
            // console.log("files length "+files.length);
            for(var i = 0; i < files.length; i++) { 
                formData.append("uploads[]", files[i], files[i].name);
            }
              //formData.append("uploads[]", files, files.name);

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        //console.log("the responc " + JSON.parse(xhr.response))
                        resolve(JSON.parse(xhr.response));
                    } else {
                        reject(xhr.response);
                    }
                }
            };

            xhr.upload.onloadstart= (event) => {
                this.xhrFileUploadStatus = true;
            };
            xhr.upload.onloadend = (event) => {
                   this.xhrFileUploadStatus = false;                
            };
            
            xhr.open("POST", url, true);
            xhr.send(formData);
        });
    }

}
