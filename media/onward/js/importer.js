var Importer = new Class({

	Implements: [Options],

	options: {
		site_id: 0,
		token: '',
		url: ''
	},

	initialize: function(options){
		this.setOptions(options);
	},
	
	doBatch: function(){
		var jsonRequest = new Request.JSON({url: this.options.url, onSuccess: function(status){
			alert(status);
		}}).get({'format': 'json', id: this.options.site_id, option: 'com_onward', task: 'site.batch'});
	}
});
