pimcore.registerNS("pimcore.plugin.participation");

pimcore.plugin.participation = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.participation";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        // alert("Participation Ready!");
    }
});

var participationPlugin = new pimcore.plugin.participation();

