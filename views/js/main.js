(function(){
    //the url of the app config is set into the data-config attr of the loader.
    var appConfig = document.getElementById('amd-loader').getAttribute('data-config');
    require([appConfig], function(){
        
        require(['jquery', 'lodash', 'context', 'urlParser'], 
            function ($, _, context, UrlParser) {

                //contextual loading
                $("body").ajaxComplete(function(event, request, settings){
                    if(settings.dataTypes.indexOf('html') > - 1){

                        var parser = new UrlParser(settings.url);
                        var paths = parser.getPaths();
                        var params = parser.getParams();
                        
                        if(paths.length >= 3){
                            var action = paths[paths.length - 1];
                            var module = paths[paths.length - 2];
                            var extension = paths[paths.length - 3];
                            
                            //loads the routing for the current extensino
                            require([extension + '/controller/routes'], function(routes){
                                if(routes && routes[module]){
                                    
                                    //get the dependencies for the current context
                                    var moduleRoutes = routes[module];
                                    var dependencies = [];
                                    if(moduleRoutes.deps){
                                        _.isArray(moduleRoutes.deps) ? dependencies.concat(moduleRoutes.deps) : dependencies.push(moduleRoutes.deps);
                                    }
                                    if(moduleRoutes.actions && moduleRoutes.actions[action]){
                                        _.isArray(moduleRoutes.actions[action]) ? dependencies.concat(moduleRoutes.actions[action]) : dependencies.push(moduleRoutes.actions[action]);
                                    }
                                    dependencies = _.map(dependencies, function(dep){
                                        return /^controller/.test(dep) ?  extension + '/' + dep : dep;
                                    });
                                    
                                    //send the 
                                    if(!_.isEmpty(params)){
                                        _.forEach(dependencies, function(dependency){
                                            var moduleConfig =  {};
                                            moduleConfig[dependency] = _.merge(params, requirejs.s.contexts._.config.config[dependency] || {});
                                            requirejs.config({ config : _.merge(params, moduleConfig) });
                                        });
                                    }
                                    
                                    //loads module and action's dependencies and start the controllers.
                                    if(dependencies.length > 0){
                                        require(dependencies, function(){
                                            _.forEach(arguments, function(dependency){
                                                if(dependency && _.isFunction(dependency.start)){
                                                    dependency.start();
                                                }
                                            });
                                        });
                                    }
                                }
                            });
                        } 
                    }
                });

                require(['controller/main']);
                
                //outside mvc routing
                if(!context.showExtension){
                    require(['controller/home']);
                } 
        });
    });
}());