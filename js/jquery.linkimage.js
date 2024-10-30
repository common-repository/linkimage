/*
    Linkimage
	
	Copyright 2013 zourbuth.com (email : zourbuth@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

(function($) {
linkimage = {	
	init : function(){
		if ($.ui) {
			$(".linkimage-sortable").each(function() {
				
				if( ! $(this).attr("data-id") )
					return;
					
				$(this).sortable({ 
					items: "div", 
					placeholder: "placeholder",
					start: function(event, ui) {
						$(".placeholder").width( ui.item.width() ).height( ui.item.height() );
					},
					update: function(event, ui) {
						var id, form, serial;
						id = $(this).attr("data-id");
						form = $(this).closest("form");
						serial = form.serialize();
						
						$.post( linkimagevar.ajaxurl, { action: linkimagevar.action, data: serial, id: id, _ajax_nonce: linkimagevar.nonce }, function( data ){
							//console.log(data);
						});
					}
				});
			});
		}
	}
};

$(document).ready(function(){linkimage.init();});
})(jQuery);