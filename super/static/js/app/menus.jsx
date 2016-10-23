import './base';
import React from 'react';
import ReactDOM from 'react-dom';
import Sortable from 'react-sortablejs';
import AjaxMenuModal from './AjaxMenuModal';

const MenuCreate = () =>
  <form className="form-group" method="POST">
    <h4>메뉴 등록</h4>
    <input type="hidden" id="command" name="command"/>
    <table className="table table-bordered table-condensed">
      <colgroup>
        <col width="250"/>
        <col width="400"/>
        <col width="80"/>
        <col width="80"/>
        <col width="80"/>
        <col width="80"/>
        <col width="80"/>
      </colgroup>
      <thead>
      <tr>
        <th>메뉴 이름</th>
        <th>메뉴 URL</th>
        <th>메뉴 깊이</th>
        <th>메뉴 순서</th>
        <th>새탭 여부</th>
        <th>사용 여부</th>
        <th>노출 여부</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td><input type="text" className="form-control" name="menu_title"/></td>
        <td><input type="text" className="form-control" name="menu_url"/></td>
        <td><input type="text" className="form-control" name="menu_deep" placeholder="0"/></td>
        <td><input type="text" className="form-control" name="menu_order" placeholder="0"/></td>
        <td>
          <select className="form-control" name="is_newtab">
            <option value="0">N</option>
            <option value="1">Y</option>
          </select>
        </td>
        <td>
          <select className="form-control" name="is_use">
            <option value="1">Y</option>
            <option value="0">N</option>
          </select>
        </td>
        <td>
          <select className="form-control" name="is_show">
            <option value="1">Y</option>
            <option value="0">N</option>
          </select>
        </td>
      </tr>
      </tbody>
    </table>
    <div className="btn-group pull-right">
      <button type="submit" className="btn btn-default">저장</button>
    </div>
  </form>;


function getMenuTypeString(menu) {
  if (!menu.is_use) return "danger";
  else if (!menu.is_show) return "warning";
  else if (menu.menu_deep == 0) return "success";
  return "";
}

class Menu extends React.Component {
  render() {
    return (
      <tr className={getMenuTypeString(this.props)}>
        <td>
          <input type="checkbox"/>
          <input type="hidden" name="id" defaultValue={this.props.id}/>
        </td>
        <td className="js_sortable_handle">{this.props.id}</td>
        <td><input type="text" className="form-control" name="menu_title" defaultValue={this.props.menu_title}/></td>
        <td><input type="text" className="form-control" name="menu_url" defaultValue={this.props.menu_url}/></td>
        <td><input type="text" className="form-control" name="menu_deep" defaultValue={this.props.menu_deep}/></td>
        <td><input type="text" className="form-control" name="menu_order" defaultValue={this.props.menu_order}/></td>
        <td>
          <select className="form-control" name="is_newtab" defaultValue={this.props.is_newtab}>
            <option value="1">Y</option>
            <option value="0">N</option>
          </select>
        </td>
        <td>
          <select className="form-control" name="is_use" defaultValue={this.props.is_use}>
            <option value="1">Y</option>
            <option value="0">N</option>
          </select>
        </td>
        <td>
          <select className="form-control" name="is_show" defaultValue={this.props.is_show}>
            <option value="1">Y</option>
            <option value="0">N</option>
          </select>
        </td>
        <td>
          <button type="button" className="btn btn-default btn-sm js_show_ajax_menus"
                  onClick={(e) => this.props.onShowAjaxMenus(this.props.id, this.props.menu_title)}>보기
          </button>
        </td>
      </tr>
    );
  }
}

Menu.propTypes = {
  id: React.PropTypes.number.isRequired,
  menu_title: React.PropTypes.string.isRequired,
  menu_url: React.PropTypes.string.isRequired,
  menu_deep: React.PropTypes.number.isRequired,
  menu_order: React.PropTypes.number.isRequired,
  is_use: React.PropTypes.bool.isRequired,
  is_newtab: React.PropTypes.bool.isRequired,
  is_show: React.PropTypes.bool.isRequired,

  onShowAjaxMenus: React.PropTypes.func.isRequired
};


function checkChangedRow($tr) {
  $tr.find('input[type=checkbox]').attr('checked', 'checked');
}

class MenuList extends React.Component {
  componentDidMount() {
    // 컬럼 변동 시 check
    $('#modifyForm input[type=text], #modifyForm select').change(function () {
      checkChangedRow($(this).parents('tr'));
    });

    // Ajax Menu 컬럼 변동 시 check
    $("#ajaxMenuBody").delegate("input[type=text]", "change", function () {
      checkChangedRow($(this).parents('tr'));
    });

    $("#ajax_url").on("keyup", function (event) {
      if (event.which == 13) {
        $("#insertAjaxUrlBtn").click();
      }
    });

    // 수정
    $('#updateBtn').click(function () {

      var container = '';
      $('#modifyForm').find('input:checked').each(function (i) {
        var id = $(this).parents('tr').find('input[name=id]').val();
        var menu_title = $(this).parents('tr').find('input[name=menu_title]').val();
        var menu_url = $(this).parents('tr').find('input[name=menu_url]').val();
        var menu_deep = $(this).parents('tr').find('input[name=menu_deep]').val();
        var menu_order = $(this).parents('tr').find('input[name=menu_order]').val();
        var is_newtab = $(this).parents('tr').find('select[name=is_newtab]').val();
        var is_use = $(this).parents('tr').find('select[name=is_use]').val();
        var is_show = $(this).parents('tr').find('select[name=is_show]').val();

        container += '<input type="text" name="menu_list[' + i + '][id]" value="' + id + '" />';
        container += '<input type="text" name="menu_list[' + i + '][menu_title]" value="' + menu_title + '" />';
        container += '<input type="text" name="menu_list[' + i + '][menu_url]" value="' + menu_url + '" />';
        container += '<input type="text" name="menu_list[' + i + '][menu_deep]" value="' + menu_deep + '" />';
        container += '<input type="text" name="menu_list[' + i + '][menu_order]" value="' + menu_order + '" />';
        container += '<input type="text" name="menu_list[' + i + '][is_newtab]" value="' + is_newtab + '" />';
        container += '<input type="text" name="menu_list[' + i + '][is_use]" value="' + is_use + '" />';
        container += '<input type="text" name="menu_list[' + i + '][is_show]" value="' + is_show + '" />';

      });
      container += '<input type="text" name="command" value="update" />\n';

      $.post('/super/menu_action.ajax', $('<form />').append(container).serializeArray(), function (returnData) {
        if (returnData.success) {
          alert(returnData.msg);
          window.location.reload();
        } else {
          alert(returnData.msg);
        }
      }, 'json');
    });
  }

  onSortEnd(evt) {
    if (evt.oldIndex === evt.newIndex) {
      return;
    }

    const $tbody = $(evt.target);

    var targetIndex = (evt.newIndex > evt.oldIndex) ? evt.newIndex - 1 : evt.newIndex + 1;
    var newOrder = $tbody.find(`tr:nth-child(${targetIndex + 1}) input[name="menu_order"]`).attr('value');

    var changedRow = $tbody.find(`tr:nth-child(${evt.newIndex + 1})`);
    changedRow.find('input[name="menu_order"]').attr('value', newOrder);
    checkChangedRow(changedRow);
  }

  showAjaxMenus(menu_id, menu_title) {
    this.modal.show(menu_id, menu_title);
  }

  render() {
    return (
      <div>
        <h4>메뉴 목록 및 수정</h4>
        <form id="modifyForm" className="form-group">
          <table className="table table-bordered table-condensed">
            <colgroup>
              <col width="20"/>
              <col width="20"/>
              <col width="250"/>
              <col width="400"/>
              <col width="80"/>
              <col width="80"/>
              <col width="80"/>
              <col width="80"/>
              <col width="80"/>
              <col width="80"/>
            </colgroup>
            <thead>
            <tr>
              <th/>
              <th>ID <span className="glyphicon glyphicon-resize-vertical"/></th>
              <th>메뉴 제목</th>
              <th>메뉴 URL</th>
              <th>메뉴 깊이</th>
              <th>메뉴 순서</th>
              <th>새탭 여부</th>
              <th>사용 여부</th>
              <th>노출 여부</th>
              <th>Ajax 관리</th>
            </tr>
            </thead>
            <Sortable
              tag="tbody"
              options={{
                handle: '.js_sortable_handle',
                onEnd: this.onSortEnd
              }}
              id='js_menu_list'>
            {this.props.menus.map((menu) =>
              <Menu key={menu.id} {...menu} onShowAjaxMenus={this.showAjaxMenus.bind(this)}/>
            )}
            </Sortable>
          </table>

          <nav className="navbar navbar-default navbar-fixed-bottom">
            <div className="pull-right">
              <button type="button" className="btn btn-primary" id="updateBtn">저장</button>
            </div>
          </nav>
        </form>

        <AjaxMenuModal ref={(e) => this.modal = e}/>
      </div>
    );
  }
}

ReactDOM.render(
  <div>
    <MenuCreate/>

    <div className="alert alert-info">
      메뉴 순서 변경시 변경할 메뉴는 해당 순서로 이동하고 기존에 위치한 메뉴는 아래로 밀려 내려갑니다.<br/>
      메뉴 수정 시 좌측의 체크박스가 자동으로 선택되고, 체크박스가 선택된 메뉴만 저장이 됩니다.
    </div>

    <MenuList menus={window.menus}/>
  </div>,
  document.getElementById('content')
);
