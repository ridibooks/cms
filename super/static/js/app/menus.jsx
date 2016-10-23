import './base';
import React from 'react';
import ReactDOM from 'react-dom';
import Sortable from 'react-sortablejs';

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
          <Sortable
            tag="tbody"
            options={{
              handle: '.js_sortable_handle',
              onEnd: this.onSortEnd
            }}
            id='js_menu_list'>
          {this.props.menus.map((menu) => <Menu key={menu.id} {...menu}/>)}
          </Sortable>
        </table>

        <nav className="navbar navbar-default navbar-fixed-bottom">
          <div className="pull-right">
            <button type="button" className="btn btn-primary" id="updateBtn">저장</button>
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

    // Ajax 메뉴 등록
    $("#insertAjaxUrlBtn").click(function () {
      if (fn_validateAjax()) {
        $("#ajax_command").val("ajax_insert");
        $.post('/super/menu_action.ajax', $("#ajaxMenuInsertForm").serialize(), function (returnData) {
          if (returnData.success) {
            alert(returnData.msg);
            $("#ajax_url").val('');
            fn_showAjaxMenus($("#ajax_menu_id").val(), $("#ajax_menu_title").val());
          } else {
            alert(returnData.msg);
          }
        }, 'json');
      }
    });

    //Ajax Menu Url 수정
    $("#updateAjaxUrlBtn").click(function () {
      fn_executeAjaxMenus('ajax_update');
    });

    //Ajax Menu Url 삭제
    $("#deleteAjaxUrlBtn").click(function () {
      fn_executeAjaxMenus('ajax_delete');
    });


    function fn_validateAjax() {
      var input_ajax_url = $("#ajaxMenuInsertForm").find("input[name=ajax_url]");
      if (input_ajax_url.val() == '') {
        alert('Ajax 메뉴 URL을 입력하여 주십시오.');
        input_ajax_url.focus();
        return false;
      }
      return true;
    }

    //Ajax 메뉴 수정 / 삭제 한다.
    function fn_executeAjaxMenus(command) {
      var container = '';
      $('#ajaxMenuUpdateForm').find("input:checked").each(function (i) {
        var id = $(this).parents('tr').find('input[type=checkbox]').val();
        var menu_id = $("#ajax_menu_id").val();
        var ajax_url = $(this).parents('tr').find('input[name=ajax_url]').val();

        container += '<input type="text" name="menu_ajax_list[' + i + '][id]" value="' + id + '" />';
        container += '<input type="text" name="menu_ajax_list[' + i + '][menu_id]" value="' + menu_id + '" />';
        container += '<input type="text" name="menu_ajax_list[' + i + '][ajax_url]" value="' + ajax_url + '" />';
      });
      container += '<input type="text" name="command" value="' + command + '" />\n';

      $.post('/super/menu_action.ajax', $('<form />').append(container).serializeArray(), function (returnData) {
        if (returnData.success) {
          alert(returnData.msg);
          fn_showAjaxMenus($("#ajax_menu_id").val(), $("#ajax_menu_title").val());
        } else {
          alert(returnData.msg);
        }
      }, 'json');
    }

    //Ajax 메뉴 목록 보기
    function fn_showAjaxMenus(menu_id, menu_title) {
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
    <MenuCreate/>

    <div className="alert alert-info">
      메뉴 순서 변경시 변경할 메뉴는 해당 순서로 이동하고 기존에 위치한 메뉴는 아래로 밀려 내려갑니다.<br/>
      메뉴 수정 시 좌측의 체크박스가 자동으로 선택되고, 체크박스가 선택된 메뉴만 저장이 됩니다.
    </div>

    <MenuList menus={window.menus}/>

    <AjaxMenuModal/>
  </div>,
  document.getElementById('content')
);
