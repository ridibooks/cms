import './base';
import React from 'react';
import ReactDOM from 'react-dom';


function getMenuTypeString(menu) {
  if (!menu.is_use) return "danger";
  else if (!menu.is_show) return "warning";
  else if (menu.menu_deep == 0) return "success";
  return "";
}

class Menu extends React.Component {
  showAjaxMenus(menu_id, menu_title) {
    $.post('/super/menu_action.ajax', {
      'menu_id': menu_id,
      'command': 'show_ajax_list'
    }, function (returnData) {
      if (returnData.success) {
        var menu_list = returnData.data;
        var html = '';
        if (menu_list.length != 0) {
          for (var i in menu_list) {
            html += '<tr>';
            html += '<td>' + menu_list[i]['id'] + '</td>';
            html += '<td><input type="checkbox" value="' + menu_list[i]['id'] + '"/></td>';
            html += '<td><input type="text" class="form-control" name="ajax_url" value="' + menu_list[i]['ajax_url'] + '"/></td>';
            html += '</tr>';
          }
        } else {
          html += '<tr><td colspan="3">등록된 Ajax 메뉴가 없습니다.</td></tr>';
        }

        $("#ajaxMenuBody").html(html);
        $("#ajaxMenuModalLabel").html(menu_title + ' Ajax 등록 및 수정');
        $("#ajax_menu_id").val(menu_id);
        $("#ajax_menu_title").val(menu_title);
        $("#ajax_url").val('');
        $("#ajaxMenuModal").modal('show');
      } else {
        alert(returnData.msg);
      }
    }, 'json');
  }

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
                  onClick={(e) => this.showAjaxMenus(this.props.id, this.props.menu_title)}>보기
          </button>
        </td>
      </tr>
    );
  }
}

Menu.propTypes = {
  id: React.PropTypes.number,
  menu_title: React.PropTypes.string,
  menu_url: React.PropTypes.string,
  menu_deep: React.PropTypes.number,
  menu_order: React.PropTypes.number,
  is_use: React.PropTypes.bool,
  is_newtab: React.PropTypes.bool,
  is_show: React.PropTypes.bool
};

class MenuList extends React.Component {
  render() {
    return (
      <form id="modifyForm" className="form-group">
        <h4>메뉴 목록 및 수정</h4>
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
          <tbody id='js_menu_list'>
          {this.props.menus.map((menu) => <Menu key={menu.id} {...menu}/>)}
          </tbody>
        </table>

        <nav className="navbar navbar-default navbar-fixed-bottom">
          <div className="pull-right">
            <button type="button" className="btn btn-primary" id="updateBtn">저장</button>
            <button type="button" className="btn btn-default" id="cancelBtn">취소</button>
          </div>
        </nav>

      </form>
    );
  }
}

class AjaxMenuModal extends React.Component {
  componentDidMount() {
    $("#ajaxMenuModal").modal({
      keyboard: true,
      show: false
    });
  }

  render() {
    return (
      <div id="ajaxMenuModal" className="modal fade" tabIndex="-1" role="dialog" aria-labelledby="ajaxMenuModalLabel"
           aria-hidden="true">
        <div className="modal-dialog">
          <div className="modal-content">
            <div className="modal-header">
              <a type="button" className="close" data-dismiss="modal" aria-hidden="true">×</a>
              <h4 id="ajaxMenuModalLabel">메뉴 Ajax 목록 및 수정</h4>
            </div>
            <div className="modal-body">
              <form id="ajaxMenuInsertForm" className="form-inline" onSubmit={() => false}>
                <input type="hidden" id="ajax_command" name="command"/>
                <input type="hidden" id="ajax_menu_id" name="menu_id"/>
                <input type="hidden" id="ajax_menu_title"/>

                <div className="form-group">
                  <input type="text" className="form-control" id="ajax_url" name="ajax_url" placeholder="Ajax 메뉴 Url 입력"/>
                  <button type="button" className="btn btn-success" id="insertAjaxUrlBtn">추가</button>
                </div>
              </form>
              <form id="ajaxMenuUpdateForm" className="form-group">
                <table className="table table-bordered table-condensed">
                  <colgroup>
                    <col width="25"/>
                    <col width="25"/>
                    <col width=""/>
                  </colgroup>
                  <thead>
                  <tr>
                    <th/>
                    <th>ID</th>
                    <th>Ajax 메뉴 URL</th>
                  </tr>
                  </thead>
                  <tbody id="ajaxMenuBody"/>
                </table>
              </form>
            </div>
            <div className="modal-footer">
              <div className="btn-group pull-left">
                <button className="btn btn-warning btn-sm" id="deleteAjaxUrlBtn">삭제</button>
              </div>
              <div className="btn-group pull-right">
                <button className="btn btn-primary btn-sm" id="updateAjaxUrlBtn">저장</button>
                <button className="btn btn-default btn-sm" data-dismiss="modal" aria-hidden="true">Close</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

ReactDOM.render(
  <div>
    <MenuList menus={window.menus}/>

    <AjaxMenuModal/>
  </div>,
  document.getElementById('content')
);
