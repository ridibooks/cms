import '../base';
import React from 'react';
import ReactDOM from 'react-dom';
import MenuList from './MenuList';

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
