$(document).ready(function() {

  var bindCollapseExpand = function(rowExpander) {
    rowExpander.unbind('click').click(function() {
      // row
      row = rowExpander.parent();

      // outer div
      outer = row.parent();

      row.toggleClass('row-expanded');
      outer.children(':not(:first)').toggle();
    });
  };

  var calcLeftPaddingByDepth = function(depth, file) {
    file = file || false;
    if (!file) {
      depth--;
    }

    return depth * 19 + 10;
  };

  var calcLeftPadding = function(path, file) {
    file = file || false;
    if (path === "0") {
      return 10;
    }
    var depth = path.split('.').length;

    return calcLeftPaddingByDepth(depth, file);
  };

  var recalcLeftPadding = function(elem) {
    pleft = calcLeftPaddingByDepth(elem.data('path').length, elem.data('folder') != 1);
    elem.css('padding-left', pleft);
  };

  var updatePath = function(elem, path) {
    elem.data('path', path);
    recalcLeftPadding(elem);
    if (elem.data('folder') == 1) {
      var outer = elem.parent();
      outer.children('.row:not(:first)').each(function(index, value) {
        updatePath($(value), path.concat(elem.data('rowId')));
      });
    }
  };

  var moveInto = function(elem, folder) {
    folder.addClass('row-expanded loading');
    folder.parent().children(':not(:first)').show();
    bindCollapseExpand(folder.find('.folder-expander'));
    $.ajax({
      url: 'update',
      method: 'POST',
      data: {
        rowId: elem.data('rowId'),
        givenId: folder.data('rowId'),
        action: 'into'
      },
    }).success(function(response) {
      if (folder.hasClass('not-loaded')) {
        if (response.data) {
          populateFolder(response.data, folder)
        }
        folder.removeClass('not-loaded');
        if (elem.data('folder') == 1) {
          elem.parent().remove();
        } else {
          elem.remove();
        }
      } else {
        var parentDiv = folder.parent();
        if (elem.data('folder') == 1) {
          elem.parent().appendTo(parentDiv);
        } else {
          elem.appendTo(parentDiv);
        }
        updatePath(elem, folder.data('path').concat(folder.data('rowId')));
      }
    }).complete(function() {
      $('.loading').removeClass('loading');
      $('.row-active').removeClass('row-active');
    });
  };

  var moveBefore = function(elem, given) {
    $.ajax({
      url: 'update',
      method: 'POST',
      data: {
        rowId: elem.data('rowId'),
        givenId: given.data('rowId'),
        action: 'before'
      },
    }).success(function(response) {
      var ins;
      if (elem.data('folder') == 1) {
        ins = elem.parent();
      } else {
        ins = elem;
      }
      if (given.data('folder') == 1) {
        ins.insertBefore(given.parent());
      } else {
        ins.insertBefore(given);
      }
      updatePath(elem, given.data('path'));
    }).complete(function() {
      $('.row-active').removeClass('row-active');
    });
  };

  var moveAfter = function(elem, given) {
    $.ajax({
      url: 'update',
      method: 'POST',
      data: {
        rowId: elem.data('rowId'),
        givenId: given.data('rowId'),
        action: 'after'
      },
    }).success(function(response) {
      var ins;
      if (elem.data('folder') == 1) {
        ins = elem.parent();
      } else {
        ins = elem;
      }
      if (given.data('folder') == 1) {
        ins.insertAfter(given.parent());
      } else {
        ins.insertAfter(given);
      }
      updatePath(elem, given.data('path'));
    }).complete(function() {
      $('.row-active').removeClass('row-active');
    });
  };

  var populateFolder = function(data, appendAfter) {
    var canDrop = function(draggable, droppable) {
      if ($.inArray(draggable.data('rowId'), droppable.data('path')) != -1) {
        return false;
      }

      return canDropInto(draggable, droppable) || canDropBefore(draggable, droppable) || canDropAfter(draggable, droppable);
    };

    var canDropInto = function(draggable, droppable) {
      if (droppable.data('folder') != 1) {
        return false;
      }

      return true;
    };

    var canDropBefore = function(draggable, droppable) {
      if (droppable.data('parent') == 0) {
        return false;
      }

      return true;
    };

    var canDropAfter = function(draggable, droppable) {
      if (droppable.data('parent') == 0) {
        return false;
      }
      if (droppable.hasClass('row-expanded')) {
        return false;
      }

      return true;
    };

    $.each(data, function(index, value) {
      var pathArr = value.path.split('.');
      var pleft = calcLeftPadding(value.path);
      var pleftFile = calcLeftPadding(value.path, true);
      var row = $('<div/>', {
        class: 'row',
        click: function() {
          $('.row-active').removeClass('row-active');
          $(this).addClass('row-active');
        },
        mousedown: function(e) { e.preventDefault(); },
      }).data('rowId', value.id)
        .data('folder', value.folder)
        .data('path', pathArr)
        .data('parent', value.parent)
      ;

      if (value.folder == 1) {
        $('<div/>', {
          class: 'folder-expander',
          click: function() {
            getChildren(value.id, row);
            row.addClass('row-expanded loading').removeClass('not-loaded');
            bindCollapseExpand($(this));
          },
        }).appendTo(row);

        $('<div/>', {
          class: 'folder-icon'
        }).appendTo(row);
        row.addClass('folder not-loaded');
        row.css('padding-left', pleft);
        row.dblclick(function() {
          row.find('.folder-expander').click();
        })
      } else {
        row.css('padding-left', pleftFile);
        $('<div/>', {class: 'file-icon'}).appendTo(row);
      }

      // lets name our file (folder)
      $('<div/>', {class: 'name'}).text(value.name).appendTo(row);

      if (appendAfter) {
        // needed for expand/collapse
        if (value.folder == 1) {
          var folderOuter = $('<div/>', {
            class: 'folder-outer',
          }).append(row);
          appendAfter.after(folderOuter);
          appendAfter = folderOuter;
        } else {
          appendAfter.after(row);
          appendAfter = row;
        }
      } else {
        // for base folder
        row.appendTo($('#tree'));
      }

      if (value.parent != 0) {
        row.draggable({
          start: function(event) {
            row.click();
          },
          cursorAt: { top: 12, left: 0 },
          helper: function(event) {
            var dragtip = $('<div/>', { class: 'dragtip' });
            dragtip.append($('<div/>', { class: 'dragtip-icon' }));
            dragtip.append($('<div/>', { class: 'dragtip-text', text: value.name }));

            return dragtip;
          },
          drag: function(event, ui) {
            var draggable = $(this);
            var droppable = $('.drop-hover'); // some way to get droppable
            var pos = droppable.position();
            var bCanDrop = canDrop($(this), droppable);
            var bCanDropInto = canDropInto($(this), droppable);
            var bCanDropBefore = canDropBefore($(this), droppable);
            var bCanDropAfter = canDropAfter($(this), droppable);

            if (pos != undefined) {
              var offsetFromTop = ui.position.top - pos.top + 12;
              if (bCanDrop) {
                if (bCanDropBefore && bCanDropAfter && !bCanDropInto) {
                  if (offsetFromTop < 12) {
                    droppable.addClass('drop-before').removeClass('drop-after');
                    droppable.unbind('drop').on('drop', function(event, ui) {
                      moveBefore(draggable, droppable);
                    });
                  } else {
                    droppable.addClass('drop-after').removeClass('drop-before');
                    droppable.unbind('drop').on('drop', function(event, ui) {
                      moveAfter(draggable, droppable);
                    });
                  }
                } else if (bCanDropBefore && offsetFromTop < 9) {
                  droppable.addClass('drop-before').removeClass('drop-after');
                  droppable.unbind('drop').on('drop', function(event, ui) {
                    moveBefore(draggable, droppable);
                  });
                } else if (bCanDropAfter && offsetFromTop > 16) {
                  droppable.addClass('drop-after').removeClass('drop-before');
                  droppable.unbind('drop').on('drop', function(event, ui) {
                    moveAfter(draggable, droppable);
                  });
                } else if (bCanDropInto) {
                  droppable.removeClass('drop-before drop-after');
                  droppable.unbind('drop').on('drop', function(event, ui) {
                    moveInto(draggable, droppable);
                  });
                } else {
                  droppable.removeClass('drop-before drop-after');
                }
              }
            }

          },
          stop: function(event, ui) {
            $('.row').unbind('drop').removeClass('drop-hover drop-before drop-after');
          },

        });
      }

      row.droppable({
        tolerance: 'intersect',
        hoverClass: 'drop-hover',
        over: function(event, ui) {
          if (canDrop(ui.draggable, $(this))) {
            $('.dragtip').addClass('drop');
          } else {
            $('.dragtip').removeClass('drop');
            $('.row').removeClass('drop-before drop-after');
          }
        },
        out: function(event, ui) {
          $(this).removeClass('drop-before drop-after drop-hover');
        }
      });

    });
  };

  var getChildren = function(parentId, appendAfter) {
    $.ajax({
      url: 'get',
      data: { parent: parentId },
    }).success(function(response) {
      if (response.data) {
        populateFolder(response.data, appendAfter);
      }
    }).complete(function() {
      $('.loading').removeClass('loading');
    });
  };

  getChildren(0);

});