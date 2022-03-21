(function (wp) {
  var badgeButton = function (props) {
    const selectedBlock = wp.data.useSelect((select) => {
      return select("core/block-editor").getSelectedBlock();
    }, []);

    // If not a paragraph don't show the button
    if (selectedBlock && selectedBlock.name !== "core/paragraph") {
      return null;
    }

    return wp.element.createElement(wp.editor.RichTextToolbarButton, {
      icon: "heart",
      title: " Like",
      onClick: function () {
        props.onChange(
          wp.richText.toggleFormat(props.value, {
            type: "formatting-extender/like",
          })
        );
      },
    });
  };

  wp.richText.registerFormatType("formatting-extender/like", {
    title: "Like",
    tagName: "span",
    className: "u-like-of",
    edit: badgeButton,
  });
})(window.wp);
