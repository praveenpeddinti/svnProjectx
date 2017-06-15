"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
var core_1 = require('@angular/core');
var TruncatePipe = (function () {
    function TruncatePipe() {
    }
    /**
     * A simple angular2 pipe which truncate strings.
     * The total number of character returned by this filter is value.length + trail.length
     *
     * @example `{{ "Some too long string" | truncate }}`
     * @example `{{ "Some too long string" | truncate :15 }}`
     * @example `{{ "Some too long string" | truncate :15:'--' }}`
     * @example `{{ "Some too long string" | truncate :15:'--':'left }}`
     * @example `{{ "Some too long string" | truncate :15:null:'middle" }}`
     *
     * @param {string} value Input string passed to the filter.
     * @param {string} [limit=10] The number of kept characters. If the `value` string has more than `limit` characters, they will be replaced by the `trail` string.
     * @param {string} [trail="..."] The string which will replace extra characters.
     * @param {string} [position="right"] The position of replacement string. Allowed values are 'left' | 'right' | 'middle'.
     * @returns {string}
     */
    TruncatePipe.prototype.transform = function (value, limit, trail, position) {
        value = value || ''; // handle undefined/null value
        limit = limit || 10;
        trail = trail || '...';
        position = position || 'right';
        if (position === 'left') {
            return value.length > limit
                ? trail + value.substring(value.length - limit, value.length)
                : value;
        }
        else if (position === 'right') {
            return value.length > limit
                ? value.substring(0, limit) + trail
                : value;
        }
        else if (position === 'middle') {
            return value.length > limit
                ? value.substring(0, limit / 2) + trail + value.substring(value.length - limit / 2, value.length)
                : value;
        }
        else {
            return value;
        }
    };
    TruncatePipe = __decorate([
        core_1.Pipe({
            name: 'truncate',
            pure: true
        }),
        core_1.Injectable(), 
        __metadata('design:paramtypes', [])
    ], TruncatePipe);
    return TruncatePipe;
}());
exports.TruncatePipe = TruncatePipe;
//# sourceMappingURL=truncate.pipe.js.map