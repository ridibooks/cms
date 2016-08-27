import './base';
import 'select2';
import 'bootstrap';

var global_tag_id;

$(function () {
  // 태그 목록 컬럼 변동 시 check
  $('#updateForm input[type=text], #updateForm select').change(function () {
    $(this).parents('tr').find('input[type=checkbox]').attr('checked', 'checked');
  });

  // 태그 목록 수정
  $('#updateBtn').click(function () {
    var container = '';
    $('#updateForm input:checked').each(function (i) {
      var id = $(this).parents('tr').find('input[name=id]').val();
      var name = $(this).parents('tr').find('input[name=name]').val();
      var is_use = $(this).parents('tr').find('select[name=is_use]').val();

      container += '<input type="text" name="tag_list[' + i + '][id]" value="' + id + '" />';
      container += '<input type="text" name="tag_list[' + i + '][name]" value="' + name + '" />';
      container += '<input type="text" name="tag_list[' + i + '][is_use]" value="' + is_use + '" />';

    });
    container += '<input type="text" name="command" value="update" />\n';

    $.post('/super/tag_action.ajax', $('<form />').append(container).serializeArray(), function (returnData) {
      if (returnData.success) {
        alert(returnData.msg);
        window.location.reload();
      } else {
        alert(returnData.msg);
      }
    }, 'json');
  });

  var tag_menu_select = $("#tag_menu_select");
  tag_menu_select.select2().change(function (e) {
    if (e.added) {
      // 권한 선택 시
      fn_executeTagMenu(e.added.id, 'mapping_tag_menu')
    } else if (e.removed) {
      // 선택된 권한 삭제 시
      if (confirm("삭제하시겠습니까?")) {
        fn_executeTagMenu(e.removed.id, 'delete_tag_menu');
      }
    }
  });

  $('#js_delete').click(() => {
    if (!confirm('선택한 항목들을 삭제하시겠습니까?')) {
      return;
    }

    $('#updateForm input:checked').map((i, e) => {

      const $tr = $(e).parents('tr');
      const tagId = $tr.find('input[name=id]').val();

      $.ajax({
        url: '/super/tags/' + tagId,
        type: 'DELETE'
      }).done((result) => {
        if (result.success) {
          $tr.detach();
        }
      });
    });
  });
});

window.showTagUsers = function (tagId) {
  $('#js_users_dialog').modal();

  // clear
  $('#tag_admins').html('불러오는 중입니다...');

  $.get('/super/tags/' + tagId + '/users', function (result) {
    if (!result.success) {
      return;
    }

    const admins = result.data;
    const admins_html = admins.map(function (admin) {
      return (`
            <li>
              <h4>
                <a class="label label-default" href="/super/users/${admin.id}" target="_blank">${admin.name}</a>
              </h4>
            </li>`
      );
    }).join(' ');

    $('#tag_admins').html(admins_html);
  });
  return false;
};

window.showTagMenus = function (tag_id) {
  $.post('/super/tag_action.ajax', { 'id': tag_id, 'command': 'show_mapping' }, function (returnData) {
    if (!returnData.success) {
      alert(returnData.msg);
      return;
    }

    global_tag_id = tag_id;

    $("#updateForm tr").attr("class", "");
    $("#tag_tr_" + tag_id).attr("class", "info");
    $("#tag_menu_div").modal();

    const menus = returnData.data.menus;
    const menus_html = menus.map(function (menu) {
      var menu_url_array = menu['menu_url'].split('#');

      var html = '<option value=" ' + menu['id'] + ' " ';
      if (menu['selected'] == 'selected') {
        html += ' selected="selected" ';
      }
      html += '>';
      html += menu['menu_title'];
      html += (menu_url_array[1] ? '#' + menu_url_array[1] : '');
      html += '</option>';
      return html;
    }).join('');

    const tag_menu_select = $("#tag_menu_select");
    tag_menu_select.html(menus_html);
    tag_menu_select.select2();

  }, 'json');

  return false;
};

// Tag에 Menu 등록 / 삭제 한다.
function fn_executeTagMenu(menu_id, command) {
  var jsonArray = {
    'tag_id': global_tag_id,
    'menu_id': menu_id,
    'command': command
  };
  $.post('/super/tag_action.ajax', jsonArray, function (returnData) {
    if (!returnData.success) {
      alert(returnData.msg);
    }
  }, 'json');
}
