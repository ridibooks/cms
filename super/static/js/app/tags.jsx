import "./base";
import React from "react";
import ReactDOM from "react-dom";

const TagCreate = () => {
  return (
    <form className="clearfix" method="POST">
      <h4>태그 등록</h4>
      <table className="table table-bordered">
        <colgroup>
          <col width="200"/>
          <col width="80"/>
        </colgroup>
        <thead>
        <tr>
          <th>태그 이름</th>
          <th>사용 여부</th>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td><input type="text" className="input-block-level" name="name"/></td>
          <td>
            <select className="input-block-level" name="is_use">
              <option value="1">Y</option>
              <option value="0">N</option>
            </select>
          </td>
        </tr>
        </tbody>
      </table>
      <div className="pull-right">
        <button type="submit" className="btn btn-primary">저장</button>
      </div>
    </form>
  );
};

const Tag = (props) => {
  return (
    <tr id={props.id} className={!props.is_use ? "danger" : ""}>
      <td>
        <input type="checkbox" name="changed"/>
        <input type="hidden" name="id" value={props.id}/>
      </td>
      <td>{props.id}</td>
      <td><input type="text" className="input-block-level" name="name" defaultValue={props.name}/></td>
      <td>{props.creator}</td>
      <td>
        <input type="checkbox" className="input-block-level" name="is_use" defaultChecked={props.is_use}/>
      </td>
      <td>{props.created_at}</td>
      <td>{props.updated_at}</td>
      <td>
        <a href='#' data-toggle="modal" data-target="#js_menus_dialog" data-tag-id={props.id}>
          {props.menus_count}
        </a>
      </td>
      <td>
        <a href="#" data-toggle="modal" data-target="#js_users_dialog" data-tag-id={props.id}>
          {props.users_count}
        </a>
      </td>
    </tr>
  );
};

Tag.propTypes = {
  id: React.PropTypes.number,
  creator: React.PropTypes.string,
  is_use: React.PropTypes.bool,
  created_at: React.PropTypes.string,
  updated_at: React.PropTypes.string,
  menus_count: React.PropTypes.number,
  users_count: React.PropTypes.number
};


class TagList extends React.Component {
  componentDidMount() {
    // 태그 목록 컬럼 변동 시 check
    $('#updateForm input[name=name], #updateForm input[name=is_use]').change(function () {
      const $tr = $(this).parents('tr');
      $tr.find('input[name=changed]').prop('checked', true);
    });
  }

  onDelete() {
    if (!confirm('선택한 항목들을 삭제하시겠습니까?')) {
      return;
    }

    $('#updateForm input[name=changed]:checked').map((i, e) => {

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
  }

  onUpdate() {
    $.when.apply($,
      $('#updateForm input[name=changed]:checked').map((i, e) => {
        const $tr = $(e).parents('tr');
        const tagId = $tr.find('input[name=id]').val();
        const data = {
          name: $tr.find('input[name=name]').val(),
          is_use: $tr.find('input[name=is_use]').prop('checked')
        };

        return $.ajax({
          url: `/super/tags/${tagId}`,
          type: 'PUT',
          data: data
        });
      })
    ).done((result) => {
      window.location.reload();
    });
  }

  render() {
    return (
      <form id="updateForm" className="form-horizontal form-inline">
        <h4>태그 목록</h4>
        <table className="table table-bordered">
          <colgroup>
            <col width="20"/>
            <col width="20"/>
            <col width=""/>
            <col width="80"/>
            <col width="80"/>
            <col width="80"/>
            <col width="80"/>
            <col width="80"/>
            <col width="80"/>
          </colgroup>
          <thead>
          <tr>
            <th></th>
            <th>ID</th>
            <th>태그 이름</th>
            <th>생성자</th>
            <th>사용 여부</th>
            <th>최초 생성일</th>
            <th>최근 수정일</th>
            <th>포함된 메뉴</th>
            <th>사용 유저 수</th>
          </tr>
          </thead>
          <tbody>
          {this.props.tags.map((tag) => <Tag key={tag.id} {...tag} data-id={tag.id}/>)}
          </tbody>
        </table>
        <div>
          <div className="pull-right">
            <button type="button" className="btn btn-danger" id="js_delete" onClick={this.onDelete}>삭제</button>
            <button type="button" className="btn btn-primary" id="updateBtn" onClick={this.onUpdate}>저장</button>
          </div>
        </div>
      </form>
    );
  }
}


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


ReactDOM.render(
  <div>
    <TagCreate />
    <TagList tags={window.tags}/>

    <MenusDialog/>
    <UsersDialog/>
  </div>,
  document.getElementById('content')
);
