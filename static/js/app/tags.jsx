import './base';
import React from 'react';
import ReactDOM from 'react-dom';

class UsersDialog extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      admins: [],
      loading: false
    };
  }

  componentDidMount() {
    $('#js_users_dialog').on('show.bs.modal', (e) => {
      const tagId = $(e.relatedTarget).data('tag-id');

      // clear
      this.setState({ admins: [], loading: true });
      //$('#tag_admins').html('불러오는 중입니다...');

      this.loadUsers(tagId);
    });
  }

  loadUsers(tagId) {
    $.get('/super/tags/' + tagId + '/users', (result) => {
      if (!result.success) {
        return;
      }

      this.setState({ admins: result.data, loading: false });
    });
  }

  renderAdmins() {
    if (this.state.loading) {
      return <span>불러오는 중입니다</span>;
    }

    return <ul id="tag_admins">
      {this.state.admins.map((admin) => (
        <li key={admin.id}>
          <h4>
            <a className="label label-default" href={"/super/users/" + admin.id} target="_blank">{admin.name}</a>
          </h4>
        </li>
      ))}
    </ul>;
  }

  render() {
    return <div id="js_users_dialog" className="modal fade" role="dialog">
      <div className="modal-dialog">
        <div className="modal-content">
          <div className="modal-header">
            <button type="button" className="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 className="modal-title">태그 사용자 관리</h4>
          </div>
          <div className="modal-body">
            {this.renderAdmins()}
          </div>
        </div>
      </div>
    </div>;
  }
}


class MenusDialog extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      menus: [],
      loading: false
    };
  }

  componentDidMount() {
    $('#js_menus_dialog').on('show.bs.modal', (e) => {
      const tagId = $(e.relatedTarget).data('tag-id');

      // clear
      this.setState({ tagId: tagId, menus: [], loading: true });

      this.loadMenus(tagId);
    });

    var tag_menu_select = $("#tag_menu_select");
    tag_menu_select.select2();

    // 권한 선택 시
    tag_menu_select.on('select2:select', (e) => {
      const data = e.params.data;
      const menuId = data.id.trim();
      this.addTagMenu(menuId)
    });

    // 선택된 권한 삭제 시
    tag_menu_select.on('select2:unselect', (e) => {
      const data = e.params.data;
      const menuId = data.id.trim();
      if (confirm("삭제하시겠습니까?")) {
        this.deleteTagMenu(menuId)
      }
    });
  }

  loadMenus(tagId) {
    $.get(`/super/tags/${tagId}/menus`, (returnData) => {
      if (!returnData.success) {
        alert(returnData.msg);
        return;
      }

      this.setState({ menus: returnData.data.menus, loading: false });

      var tag_menu_select = $("#tag_menu_select");
      tag_menu_select.change();

    }, 'json');

    return false;
  }

  addTagMenu(menuId) {
    $.post(`/super/tags/${this.state.tagId}/menus/${menuId}`,
      function (returnData) {
        if (!returnData.success) {
          alert(returnData.msg);
        }
      }
    );
  }

  deleteTagMenu(menuId) {
    $.ajax({
      url: `/super/tags/${this.state.tagId}/menus/${menuId}`,
      type: 'DELETE'
    });
  }

  renderSelect() {
    const selected = this.state.menus
      .filter((menu) => menu.selected === 'selected')
      .map((menu) => menu.id);

    return <select style={{ width: '100%' }} id="tag_menu_select" data-placeholder="권한 추가하기" multiple={true} value={selected} onChange={() => {}}>
      {this.state.menus.map(this.renderOption)}
    </select>;
  }

  renderOption(menu) {
    const menuUrls = menu['menu_url'].split('#');
    return <option key={menu.id} value={menu.id}>
      {menu.menu_title}{menuUrls[1] ? '#' + menuUrls[1] : ''}
    </option>;
  }

  render() {
    return <div id="js_menus_dialog" className="modal fade">
      <div className="modal-dialog">
        <div className="modal-content">
          <div className="modal-header">
            <button type="button" className="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 className="modal-title">태그 메뉴 관리</h4>
          </div>
          <div className="modal-body">
            {this.state.loading ? <p>불러오는 중입니다...</p> : '' }
            {this.renderSelect()}
          </div>
        </div>
      </div>
    </div>;
  }
}


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

ReactDOM.render(
  <div>
    <MenusDialog/>
    <UsersDialog/>
  </div>,
  document.getElementById('content')
);
