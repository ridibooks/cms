import './base';
import React from 'react';
import ReactDOM from 'react-dom';


function getMenuTypeString(menu) {
  if (menu.is_use == 0) return "danger";
  else if (menu.is_show == 0) return "warning";
  else if (menu.menu_deep == 0) return "success";
  return "";
}

const Menu = (props) =>
  <tr className={getMenuTypeString(props)}>
    <td>
      <input type="checkbox"/>
      <input type="hidden" name="id" value={props.id}/>
    </td>
    <td className="js_sortable_handle">{props.id}</td>
    <td><input type="text" className="form-control" name="menu_title" value={props.menu_title}/></td>
    <td><input type="text" className="form-control" name="menu_url" value={props.menu_url}/></td>
    <td><input type="text" className="form-control" name="menu_deep" value={props.menu_deep}/></td>
    <td><input type="text" className="form-control" name="menu_order" value={props.menu_order}/></td>
    <td>
      <select className="form-control" name="is_newtab" defaultValue={props.is_newtab}>
        <option value="1">Y</option>
        <option value="0">N</option>
      </select>
    </td>
    <td>
      <select className="form-control" name="is_use" defaultValue={props.is_use}>
        <option value="1">Y</option>
        <option value="0">N</option>
      </select>
    </td>
    <td>
      <select className="form-control" name="is_show" defaultValue={props.is_show}>
        <option value="1">Y</option>
        <option value="0">N</option>
      </select>
    </td>
    <td>
      <button type="button" className="btn btn-default btn-sm js_show_ajax_menus"
              data-menu-id={props.id}
              data-menu-title={props.menu_title}>보기
      </button>
    </td>
  </tr>;

Menu.propTypes = {
  id: React.PropTypes.number,
  menu_title: React.PropTypes.string,
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
            <th></th>
            <th>ID <span className="glyphicon glyphicon-resize-vertical"></span></th>
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
    )
  }
}

ReactDOM.render(
  <div>
    <MenuList menus={window.menus}/>
  </div>,
  document.getElementById('content')
);
