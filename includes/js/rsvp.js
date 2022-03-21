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
      icon: "calendar",
      title: " I'm going",
      onClick: function () {
        props.onChange(
          wp.richText.toggleFormat(props.value, {
            type: "formatting-extender/rsvp",
          })
        );
      },
    });
  };

  wp.richText.registerFormatType("formatting-extender/rsvp", {
    attributes: {
      value: "yes",
    },
    title: "Like",
    tagName: "data",
    className: "p-rsvp",
    edit: badgeButton,
  });
})(window.wp);
