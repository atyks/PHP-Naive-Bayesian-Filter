<?php
/*
  ***** BEGIN LICENSE BLOCK *****
   This file is part of PHP Naive Bayesian Filter.

   The Initial Developer of the Original Code is
   Loic d'Anterroches [loic_at_xhtml.net].
   Portions created by the Initial Developer are Copyright (C) 2003
   the Initial Developer. All Rights Reserved.

   Contributor(s):

   PHP Naive Bayesian Filter is free software; you can redistribute it
   and/or modify it under the terms of the GNU General Public License as
   published by the Free Software Foundation; either version 2 of
   the License, or (at your option) any later version.

   PHP Naive Bayesian Filter is distributed in the hope that it will
   be useful, but WITHOUT ANY WARRANTY; without even the implied
   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
   See the GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Foobar; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

   Alternatively, the contents of this file may be used under the terms of
   the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
   in which case the provisions of the LGPL are applicable instead
   of those above.

  ***** END LICENSE BLOCK *****
*/

/** Access to the storage of the data for the filter.

To avoid dependency with respect to any database, this class handle all the
access to the data storage. You can provide your own class as long as
all the methods are available. The current one rely on a MySQL database.

methods:
    - array getCategories()
    - bool  wordExists(string $word)
    - array getWord(string $word, string $categoryid)

*/
class NaiveBayesianStorage
{
    var $con = null;

    function NaiveBayesianStorage($user, $pwd , $server, $dbname)
    {
    	include_once dirname(__FILE__).'/class.mysql.php';
    	$this->con = new Connection($user, $pwd , $server, $dbname);
    	return true;

    }

    /** get the list of categories with basic data.
    
        @return array key = category ids, values = array(keys = 'probability', 'word_count')
    */
    function getCategories()
    {
        $categories = array();
        $rs = $this->con->select('SELECT * FROM nb_categories');
        while (!$rs->EOF()) {
            $categories[$rs->f('category_id')] = array('probability' => $rs->f('probability'),
                                                       'word_count'  => $rs->f('word_count')
                                                );
            $rs->moveNext();
        }
        return $categories;
    }

    /** see if the word is an already learnt word.
        @return bool
        @param string word
    */
    function wordExists($word)
    {
        $rs = $this->con->select("SELECT * FROM nb_wordfreqs WHERE word='".$this->con->escapeStr($word)."'");
        return !$rs->isEmpty();
    }

    /** get details of a word in a category.
        @return array ('count' => count)
        @param  string word
        @param  string category id
    */
    function getWord($word, $category_id)
    {
        $details = array();
        $rs = $this->con->select("SELECT * FROM nb_wordfreqs WHERE
                                    word='".$this->con->escapeStr($word)."' AND
                                    category_id='".$this->con->escapeStr($category_id)."'");
        if ($rs->isEmpty()) $details['count'] = 0;
        else $details['count'] = $rs->f('count');
        return $details;
    }

    /** update a word in a category.
    If the word is new in this category it is added, else only the count is updated.

        @return bool success
        @param string word
        @param int    count
        @paran string category id
    */
    function updateWord($word, $count, $category_id)
    {
    	$oldword = $this->getWord($word, $category_id);
    	if (0 == $oldword['count']) {
            return $this->con->execute("INSERT INTO nb_wordfreqs (word, category_id, count) VALUES
                                ('".$this->con->escapeStr($word)."',
                                 '".$this->con->escapeStr($category_id)."',
                                 '".$this->con->escapeStr((int)$count)."')");
        } else {
            return $this->con->execute("UPDATE nb_wordfreqs SET count+=".(int)$count."
                                        WHERE category_id = '".$this->con->escapeStr($category_id)."'
                                        AND word = '".$this->con->escapeStr($word)."'");
        }
    }

    /** remove a word from a category.

        @return bool success
        @param string word
        @param int  count
        @param string category id
    */
    function removeWord($word, $count, $category_id)
    {
    	$oldword = $this->getWord($word, $category_id);
    	if (0 != $oldword['count'] && 0 >= ($oldword['count']-$count)) {
            return $this->con->execute("DELETE FROM nb_wordfreqs WHERE
                                word='".$this->con->escapeStr($word)."' AND
                                category_id='".$this->con->escapeStr($category_id)."'");
        } else {
            return $this->con->execute("UPDATE nb_wordfreqs SET count-=".(int)$count."
                                        WHERE category_id = '".$this->con->escapeStr($category_id)."'
                                        AND word = '".$this->con->escapeStr($word)."'");
        }
    }

    /** update the probabilities of the categories and word count.
    This function must be run after a set of training

        @return bool sucess
    */
    function updateProbabilities()
    {
    	// first update the word count of each category
        $rs = $this->con->select("SELECT category_id, SUM(count) AS total FROM nb_wordfreqs WHERE 1 GROUP BY category_id");
        $total_words = 0;
        while (!$rs->EOF()) {
            $total_words += $rs->f('total');
            $rs->moveNext();
        }
        $rs->moveStart();
        if ($total_words == 0) {
            $this->con->execute("UPDATE nb_categories SET word_count=0, probability=0 WHERE 1");
            return true;
        }
        while (!$rs->EOF()) {
            $proba = $rs->f('total')/$total_words;
            $this->con->execute("UPDATE nb_categories SET word_count=".(int)$rs->f('total').",
                                        probability=".$proba."
                                        WHERE category_id = '".$rs->f('category_id')."'");
            $rs->moveNext();
        }
        return true;
    }

    /** save a reference in the database.

        @return bool success
        @param  string reference if, must be unique
        @param  string category id
        @param  string content of the reference
    */
    function saveReference($doc_id, $category_id, $content)
    {
        return $this->con->execute("INSERT INTO nb_references (id, category_id, content) VALUES
                                ('".$this->con->escapeStr($doc_id)."',
                                 '".$this->con->escapeStr($category_id)."',
                                 '".$this->con->escapeStr($content)."')");
    }

    /** get a reference from the database.

        @return array  reference( category_id => ...., content => ....)
        @param  string id
    */
    function getReference($doc_id)
    {
        $ref = array();
        $rs = $this->con->select("SELECT * FROM nb_references WHERE id='".$this->con->escapeStr($doc_id)."'");
        if ($rs->isEmpty()) return $ref;
        $ref['category_id'] = $rs->f('category_id');
        $ref['content'] = $rs->f('content');
        $ref['id'] = $rs->f('id');
        return $ref;
    }

    /** remove a reference from the database

        @return bool sucess
        @param  string reference id
    */
    function removeReference($doc_id)
    {
        return $this->con->execute("DELETE FROM nb_references WHERE id='".$this->con->escapeStr($doc_id)."'");
    }



}

?>
