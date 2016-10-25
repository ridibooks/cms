import React from 'react';


const Tag = (props) =>
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
  </tr>;

Tag.propTypes = {
  id: React.PropTypes.number,
  creator: React.PropTypes.string,
  is_use: React.PropTypes.bool,
  created_at: React.PropTypes.string,
  updated_at: React.PropTypes.string,
  menus_count: React.PropTypes.number,
  users_count: React.PropTypes.number
};


export default class TagList extends React.Component {
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
