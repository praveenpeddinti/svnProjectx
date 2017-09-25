import {UrlSerializer, UrlTree, DefaultUrlSerializer} from '@angular/router';

export class CustomUrlSerializer implements UrlSerializer {
    parse(url: any): UrlTree {
        let dus = new DefaultUrlSerializer();
        return dus.parse(url);
    }

    serialize(tree: UrlTree): any {
        let dus = new DefaultUrlSerializer(),
            path = dus.serialize(tree);
            //console.log("==Serialized Path=="+path);
        // use your regex to replace as per your requirement.
        //let replace={'/%3F/g':'?','/%3D/g':'='};
        path=decodeURIComponent(path);
        return path;
        //return path.replace(;
    }
}