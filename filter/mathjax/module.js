M.filter_mathjax = {
    init: function (Y, mathjaxroot) {
        var nodes;
        MathJax.Hub.Config({
            // we will pass on to the typesetter the elements that have
            // a .filter-mathjax class applied, which avoids MathJax
            // trolling through the entire document
            skipStartupTypeset: true,
            
            root: mathjaxroot
        });
        
        nodes = Y.all('.filter-mathjax');
        if (!nodes.isEmpty()) {
            MathJax.Hub.Queue(["Typeset", MathJax.Hub, nodes.getDOMNodes(), function() {}]);
        }
        MathJax.Hub.Configured();
    }
};
