import '../base';
import React from 'react';
import ReactDOM from 'react-dom';
import TagList from './TagList';
import UsersDialog from './UsersDialog';
import MenusDialog from './MenusDialog';

const TagCreate = () =>
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
  </form>;


ReactDOM.render(
  <div>
    <TagCreate />
    <TagList tags={window.tags}/>

    <MenusDialog/>
    <UsersDialog/>
  </div>,
  document.getElementById('content')
);
