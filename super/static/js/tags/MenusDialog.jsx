import React from 'react';

export default class MenusDialog extends React.Component {
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
    $.ajax({
      url: `/super/tags/${this.state.tagId}/menus/${menuId}`,
      type: 'PUT',
      success: function (returnData) {
        if (!returnData.success) {
          alert(returnData.msg);
        }
      }
    });
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

    return <select style={{ width: '100%' }} id="tag_menu_select" data-placeholder="권한 추가하기" multiple={true} value={selected} onChange={() => {
    }}>
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
