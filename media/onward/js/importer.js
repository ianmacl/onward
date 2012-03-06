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
			status.each(function(item) {
				if (item.total == 0) {
					progressBars['progress_' + item.asset].set(100);
				} else {
					progressBars['progress_' + item.asset].set(Math.round(item.offset / item.total * 100));
				}
			});
		}, onFailure: function() { alert('test')}}).get({'format': 'json', id: this.options.site_id, option: 'com_onward', task: 'site.batch'});
	}
});
