import React from 'react';

export default class UsersDialog extends React.Component {
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
