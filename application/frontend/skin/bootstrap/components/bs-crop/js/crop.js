/**
 * Crop
 *
 * @module ls/crop
 *
 * @license   GNU General Public License, version 2
 * @copyright 2013 OOO "ЛС-СОФТ" {@link http://livestreetcms.com}
 * @author    Denis Shakhov <denis.shakhov@gmail.com>
 */

(function($) {
    "use strict";

    $.widget( "livestreet.bsCrop", $.livestreet.lsComponent, {
        /**
         * Дефолтные опции
         */
        options: {
            viewMode: 2,
            autoCropArea: 0.5,
            guides: false,
            center: false,
            background: false,
            rotatable: false,
            zoomable: false,
            scalable: false
        },

        /**
         * Конструктор
         *
         * @constructor
         * @private
         */
        _create: function () {
            this._super();
            console.log(this.options)
            this.element.cropper(this.options);
           // let cropper = new Cropper(this.element.get(0),this.options);
        },

        /**
         * 
         */
        getImage: function() {
            return this.element;
        },

        /**
         * 
         */
        getImageData: function() {
            return this.element.cropper('getImageData');
        },

        /**
         * 
         */
        getCanvasData: function() {
            return this.element.cropper('getCanvasData');
        },

        /**
         * 
         */
        getSelection: function() {
            var data = this.element.cropper('getData');

            return {
                x: data.x,
                y: data.y,
                x2: data.x + data.width,
                y2: data.y + data.height
            };
        },
        
        getDataOriginal: function() {
            return this.element.cropper('getData');
        },

        /**
         * 
         */
        getCanvas: function() {
            return this.element.cropper('getCroppedCanvas');

        }
    });
})(jQuery);