/**
 * 根据字符串类型的方法名 调用对应的js function
 * executeFunctionByName("My.Namespace.functionName", window, arguments);
 * executeFunctionByName("Namespace.functionName", My, arguments);
 *
 * @url http://stackoverflow.com/questions/359788/how-to-execute-a-javascript-function-when-i-have-its-name-as-a-string
 * @param functionName
 * @param context
 * @returns {*}
 */

var smvcJSUtil = {
    executeFunctionByName: function (functionName, context /*, args */){ //对调 functionName function
        var args = [].slice.call(arguments).splice(2);
        var namespaces = functionName.split(".");
        var func = namespaces.pop();
        for (var i = 0; i < namespaces.length; i++) {
            context = context[namespaces[i]];
        }
        return context[func].apply(this, args);
    },
    functionExistsByName: function(functionName) { //判断制定字符串是否为function
        return functionName in window && typeof window[functionName] == 'function'
    }
};