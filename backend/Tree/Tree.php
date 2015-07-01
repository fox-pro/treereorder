<?php

namespace Tree;

use DI\DI;

class Tree extends DI
{
    /**
     * Json response
     *
     * @param mixed $data
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonErrorResponse()
    {
        return $this->jsonResponse(array("status" => "error"));
    }

    private function jsonSuccessResponse($data)
    {
        return $this->jsonResponse(array("status" => "success", "data" => $data));
    }

    private function getJsonRequest()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    /**
     * gets children by parent id
     *
     * json params:
     *   parent    default - 0
     */
    public function get($parent = 0)
    {
        $pdo = $this->di->get('pdo');

        $stmt = $pdo->prepare('SELECT * FROM tree WHERE parent=? ORDER BY sort ASC');
        $stmt->execute(array($parent));
        $result = $stmt->fetchAll();
        if (!$result) {
            return $this->jsonErrorResponse();
        }

        return $this->jsonSuccessResponse($result);
    }

    /**
     * Updates a row (moves) before/after given row, insert into folder-row
     *
     * json params:
     *   rowId     row id to move
     *   givenId   given row id
     *   action    "after"|"before"|"into" default - "after"
     */
    public function update()
    {
        $params = $_POST;
        if (!isset($params['rowId']) || !isset($params['givenId'])) {
            return $this->jsonErrorResponse();
        }

        $pdo = $this->di->get('pdo');

        // we need old parentId of row to fix sort indexes
        $stmt = $pdo->prepare('SELECT parent FROM tree WHERE id=?');
        $stmt->execute(array(5));
        $row = $stmt->fetch();
        $oldParentId = $row['parent'];

        // action
        // $result will be new parent id if all is ok
        if (!isset($params['action']) || $params['action'] == "after") {
            $result = $this->move($params['rowId'], $params['givenId']);
        } else if ($params['action'] == "before") {
            $result = $this->move($params['rowId'], $params['givenId'], true);
        } else if ($params['action'] == "into") {
            $result = $this->insert($params['rowId'], $params['givenId']);
        } else {
            return $this->jsonErrorResponse();
        }

        // update sort indexes ftw
        $pdo->query('SET @i = 0');
        $stmt = $pdo->prepare('UPDATE tree SET sort=(@i := @i+1) WHERE parent=? ORDER BY sort ASC');
        $stmt->execute(array($oldParentId));

        // return new json data to be sure
        if ($result) {
            return $this->get($result);
        }

        return $this->jsonErrorResponse();
    }

    /**
     * Moves a row after or before given row
     *
     * @param integer $movedId          Moving row's id
     * @param integer $insertAfterId    Given row's id
     * @param bool    $before           If true -> move before given row, otherwise -> after
     */
    private function move($movedId, $givenId, $before = false)
    {
        $sortFix = $before ? 0 : 1;
        $pdo = $this->di->get('pdo');

        // lets find parentId, path and sort index of given row
        $stmt = $pdo->prepare('SELECT parent, path, sort FROM tree WHERE id=?');
        $stmt->execute(array($givenId));
        $row = $stmt->fetch();
        $parentId = $row['parent'];
        if ($parentId == 0) {
            return false;
        }
        $pathArr = explode('.', $row['path']);
        if ($this->checkIfParent($movedId, $pathArr)) {
            return false;
        }
        $givenSortIndex = $row['sort'];


        // update sort indexes after given row
        $stmt = $pdo->prepare('UPDATE tree SET sort = sort + 1 WHERE parent=? AND sort >= ?');
        $result1 = $stmt->execute(array($parentId, $givenSortIndex + $sortFix));

        // and now update parent and sort index of moving row
        $stmt = $pdo->prepare('UPDATE tree SET parent=?, path=?, sort=? WHERE id=?');
        $result2 = $stmt->execute(array($parentId, implode('.', $pathArr), $givenSortIndex + $sortFix, $movedId));

        $result3 = $this->updateChildrenPaths($movedId, $pathArr);

        // return new parent id if all is ok
        return $result1 && $result2 && $result3 ? $parentId : false;
    }

    /**
     * Insert a row into folder (at the end as in example)
     *
     * @param integer $rowId       Moving row's id
     * @param integer $folderId    Given folder's id
     */
    private function insert($rowId, $folderId)
    {
        $pdo = $this->di->get('pdo');

        // lets check folder or not
        // and check path
        $stmt = $pdo->prepare('SELECT path, folder FROM tree WHERE id=?');
        $stmt->execute(array($folderId));
        $row = $stmt->fetch();
        if (!$row['folder']) {
            return false;
        }
        $pathArr = explode('.', $row['path']);
        if ($this->checkIfParent($rowId, $pathArr)) {
            return false;
        }
        $pathArr[] = $folderId;

        // lets find new sort index
        // assume all sort indexes is ok, as we always do
        // also it'll be ok for empty folders
        $stmt = $pdo->prepare('SELECT count(*) as count FROM tree WHERE parent=?');
        $stmt->execute(array($folderId));
        $row = $stmt->fetch();
        $newSort = $row['count'] + 1;

        // and move row into folder
        $stmt = $pdo->prepare('UPDATE tree SET parent=?, path=?, sort=? WHERE id=?');
        $result = $stmt->execute(array($folderId, implode('.', $pathArr), $newSort, $rowId));

        $result2 = $this->updateChildrenPaths($rowId, $pathArr);

        // return new parent id if all is ok
        return $result && $result2 ? $folderId : false;
    }

    private function checkIfParent($id, $pathArr)
    {
        return in_array($id, $pathArr);
    }

    private function updateChildrenPaths($parentId, $pathArr)
    {
        // add parentId to pathArr to update children with it
        $pathArr[] = $parentId;
        $pdo = $this->di->get('pdo');
        $stmt = $pdo->prepare('UPDATE tree SET path=? WHERE parent=?');
        $result = $stmt->execute(array(implode('.', $pathArr), $parentId));

        $stmt = $pdo->prepare('SELECT id FROM tree WHERE parent=? and folder=1');
        $stmt->execute(array($parentId));
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $result = $result && $this->updateChildrenPaths($row['id'], $pathArr);
        }

        return $result;
    }

}